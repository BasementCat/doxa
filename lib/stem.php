<?php
	/*For a hell of a lot more information on how this works, check out
	 * http://snowball.tartarus.org/algorithms/english/stemmer.html which is where
	 * most of the documentation in these comments comes from.
	 */
	function stem_suffix($word, $suffix, $r1=null, $r2=null){
		$suffixes=is_array($suffix)?$suffix:preg_split("#\W+#", $suffix);
		if(preg_match("#^(.*?)(".implode("|", $suffixes).")\$#", $word, $matches)){
			list($word, $wordpart, $suffix)=$matches;
			if($r1!==null&&($r1>=strlen($word)||strpos($word, $suffix, $r1)===false)) return false;
			if($r2!==null&&($r2>=strlen($word)||strpos($word, $suffix, $r2)===false)) return false;
			return $matches;
		}else return false;
	}
	
	function stem_porter2($word){
		$vowels='aeiouy';
		$vowel_re="[{$vowels}]";
		$not_vowel_re="[^{$vowels}]";
		$double_re='([bdfgmnprt])\1';
		$li_end_re='[cdeghkmnrt]';

		$short_syll_re="^{$vowel_re}{$not_vowel_re}|{$not_vowel_re}{$vowel_re}(?![wxY]){$not_vowel_re}";
		$short_word_re="{$short_syll_re}\$";

		$word=strtolower($word); //uppercase letters are used for vowel marking
		if(strlen($word)<=2) return $word;

		//Step -4: special exceptions
		$stepn4_stem=array('skis'=>'ski', 'skies'=>'sky', 'dying'=>'die',
			'lying'=>'lie', 'tying'=>'tie', 'idly'=>'idl', 'gently'=>'gentl',
			'ugly'=>'ugli', 'early'=>'earli', 'only'=>'onli', 'singly'=>'singl',
			'sky'=>'sky', 'news'=>'news', 'howe'=>'howe', 'atlas'=>'atlas',
			'cosmos'=>'cosmos', 'bias'=>'bias', 'andes'=>'andes');
		if(isset($stepn4_stem[$word])) return $stepn4_stem[$word];

		//Step -3: remove initial ' if it is present
		$word=preg_replace("#^'#", '', $word);

		//Step -2: set initial y, or y after a vowel to Y (to mark as a consonant)
		$word=preg_replace("#^y#", 'Y', $word);
		$word=preg_replace("#(?={$vowel_re})y#", 'Y', $word);

		//Step -1: find regions R1 and R2
		$R1=strlen($word);
		if(preg_match("#^(gener|commun|arsen)(.*)\$#", $word, $matches))
			$R1=strlen($word)-strlen($matches[2]);
		elseif(preg_match("#{$vowel_re}{$not_vowel_re}(.*)\$#", $word, $matches))
			$R1=strlen($word)-strlen($matches[1]);
		$R2=strlen($word);
		if($R1&&preg_match("#{$vowel_re}{$not_vowel_re}(.*)\$#", $R1, $matches))
			$R2=strlen($word)-strlen($matches[1]);

		//Step 0: Search for the longest suffix of "'s'", "'s", or "'" and remove
		$word=preg_replace("#('s'|'s|')\$#", '', $word);

		/*Step 1a: search for the longest of the following suffixes and perform
		 * the indicated action:
		 * "sses" -> "ss"
		 * "ied" or "ies": if preceded by 2+ chars, replace with "i" else with "ie"
		 * "s": remove if preceded by a vowel but not immediately before the "s"
		 * "us" or "ss": do nothing
		 */
		/*if(preg_match("#^(.*?)(sses|ied|ies|us|ss|s)\$#", $word, $matches)){
			list($original, $wordpart, $suffix)=$matches;*/
		if(list($original, $wordpart, $suffix)=stem_suffix($word, 'sses, ied, ies, us, ss, s')){
			switch($suffix){
				case 'sses':
					$word=$wordpart.'ss';
					break;
				case 'ied':
				case 'ies':
					if(strlen($wordpart)>1)
						$word=$wordpart.'i';
					else
						$word=$wordpart.'ie';
					break;
				case 's':
					if(preg_match("#{$vowel_re}.+\$#", $wordpart))
						$word=$wordpart;
					else
						$word=$wordpart.'s';
					break;
			}
		}

		/*Step 1a-1: some more exceptions...*/
		$step1a1_stem=array('inning', 'outing', 'canning', 'herring', 'earring',
			'proceed', 'exceed', 'succeed');
		if(in_array($word, $step1a1_stem)) return $word;

		/*Step 1b: search for the longest of the following suffixes and perform the
		 * indicated action:
		 * "eed" or "eedly": replace by "ee" if they're within R1
		 * "ed", "edly", "ing", or "ingly":
		 * - delete if the preceding word part contains a vowel, and then:
		 * -- if the word ends with "at", "bl", or "iz", append "e". otherwise:
		 * -- if the word ends with a double, remove the last letter.  otherwise:
		 * -- if the word is short (?) append "e"
		 */
		/*if(preg_match("#^(.*?)(eed|eedly|edly|ed|ingly|ing)\$#", $word, $matches)){
			list($original, $wordpart, $suffix)=$matches;*/
		if(list($original, $wordpart, $suffix)=stem_suffix($word, 'eedly, eed, edly, ed, ingly, ing')){
			switch($suffix){
				case 'eed':
				case 'eedly':
					//if(preg_match("#{$suffix}\$#", $R1))
					if(stem_suffix($word, $suffix, $R1))
						$word=$wordpart.'ee';
					else
						$word=$wordpart;
					break;
				case 'ed':
				case 'edly':
				case 'ing':
				case 'ingly':
					if(preg_match("#{$vowel_re}#", $wordpart))
						$word=$wordpart;
					else
						$word=$wordpart.$suffix;
					if(preg_match("#(at|bl|iz)\$#", $word))
						$word.='e';
					elseif(preg_match("#{$double_re}\$#", $word))
						$word=preg_replace("#.\$#", '', $word);
					elseif(($R1>=strlen($word))&&preg_match("#{$short_word_re}#", $word))
						$word.='e';
					break;
			}
		}

		/*Step 1c: replace "y" or "Y" occuring at the end of the word IF it is
		 * preceded by a non-vowel which is not also the first letter of the word
		 * (i.e. the word is >2 chars)
		 */
		$word=preg_replace("#(.+{$not_vowel_re})[yY]\$#", '$1i', $word);

		/*Step 2: search for the following suffixes, if they are present and also
		 * in R1, perform the indicated action:
		 * "tional"->"tion"
		 * "enci"->"ence"
		 * "anci"->"ance"
		 * "abli"->"able"
		 * "entli"->"ent"
		 * "izer" or "ization"->"ize"
		 * "ational", "ation", or "ator"->"ate"
		 * "alism", "aliti", or "alli"->"al"
		 * "fulness" or "fulli"->"ful"
		 * "ousli" or "ousness"->"ous"
		 * "iveness" or "iviti"->"ive"
		 * "biliti" or "bli" ->"ble"
		 * "ogi": ->"og" IF preceded by "l"
		 * "lessli"->"less"
		 * "li": delete IF preceded by a li-ending
		 */
		$step2_repl=array('tional'=>'tion', 'enci'=>'ence', 'anci'=>'ance',
			'abli'=>'able', 'entli'=>'ent', 'izer'=>'ize', 'ization'=>'ize',
			'ational'=>'ate', 'ation'=>'ate', 'ator'=>'ate', 'alism'=>'al',
			'aliti'=>'al', 'alli'=>'al', 'fulness'=>'ful', 'fulli'=>'ful',
			'ousli'=>'ous', 'ousness'=>'ous', 'iveness'=>'ive', 'iviti'=>'ive',
			'biliti'=>'ble', 'bli'=>'ble', 'lessli'=>'less');
		/*if(preg_match("#^(.*?)(".implode("|", $step2_repl)."|ogi|li)\$#", $word, $matches)){
			list($original, $wordpart, $suffix)=$matches;*/
		if(list($original, $wordpart, $suffix)=stem_suffix($word, array_merge(array_keys($step2_repl), array('ogi', 'li')))){
			//if(preg_match("#{$suffix}\$#", $R1)){
			if(stem_suffix($word, $suffix, $R1)){
				if(isset($step2_repl[$suffix]))
					$word=$wordpart.$step2_repl[$suffix];
				elseif($suffix=='ogi'&&preg_match("#l\$#", $wordpart))
					$word=$wordpart.'og';
				elseif($suffix=='li'&&preg_match("#{$li_end_re}\$#", $wordpart))
					$word=$wordpart;
			}
		}

		/*Step 3: find the longest of the following suffixes, and if they're found
		 * and also in R1, perform the indicated action
		 */
		/*if(preg_match("#^(.*?)(ational|tional|alize|icate|iciti|ical|ful|ness|ative)\$#", $word, $matches)){
			list($original, $wordpart, $suffix)=$matches;*/
		if(list($original, $wordpart, $suffix)=stem_suffix($word, 'ational, tional, alize, icate, iciti, ical, ful, ness, ative')){
			//if(preg_match("#{$suffix}\$#", $R1)){
			if(stem_suffix($word, $suffix, $R1)){
				switch($suffix){
					case 'tional':
						$word=$wordpart.'tion';
						break;
					case 'ational':
						$word=$wordpart.'ate';
						break;
					case 'alize':
						$word=$wordpart.'al';
						break;
					case 'icate':
					case 'iciti':
					case 'ical':
						$word=$wordpart.'ic';
						break;
					case 'ful':
					case 'ness':
						$word=$wordpart;
						break;
					case 'ative':
						if(preg_match("#ative\$#", $R2))
							$word=$wordpart;
						else
							$word=$wordpart.$suffix;
						break;
				}
			}
		}

		/*Step 4: search for the longest of the following suffixes and if found and
		 * in R2 (NOT R1!) perform the indicated action:
		 * al, ance, ence, er, ic, able, ible, ant, ement, ment, ent, ism, ate, iti, ous, ive, ize:
		 * -- delete
		 * "ion": delete IF preceded by "s" or "t"
		 */
		/*if(preg_match("#^(.*?)(al|ance|ence|er|ic|able|ible|ant|ement|ment|ent|ism|ate|iti|ous|ive|ize|ion)\$#", $word, $matches)){
			list($original, $wordpart, $suffix)=$matches;*/
		if(list($original, $wordpart, $suffix)=stem_suffix($word, 'al, ance, ence, er, ic, able, ible, ant, ement, ment, ent, ism, ate, iti, ous, ive, ize, ion')){
			//if(preg_match("#{$suffix}\$#", $R2)){
			if(stem_suffix($word, $suffix, null, $R2)){
				if($suffix=='ion'){
					if(preg_match("#[st]\$#", $wordpart))
						$word=$wordpart;
					else
						$word=$wordpart.$suffix;
				}else $word=$wordpart;
			}
		}

		/*Step 5: search for the following suffixes and if found perform the
		 * indicated action:
		 * "e": delete if in R2, or if in R1 AND NOT preceded by a short syllable
		 * "l": delete if in R2 AND preceded by "l"
		 */
		if(list($original, $wordpart, $suffix)=stem_suffix($word, 'e, l')){
			if($suffix=='e'){
				if(stem_suffix($word, $suffix, null, $R2)||(stem_suffix($word, $suffix, $R1)&&!preg_match("#{$short_syll_re}\$#", $wordpart)))
					$word=$wordpart;
				else
					$word=$wordpart.$suffix;
			}elseif($suffix=='l'){
				if(stem_suffix($word, $suffix, null, $R2)&&preg_match("#l\$#", $wordpart))
					$word=$wordpart;
				else
					$word=$wordpart.$suffix;
			}
		}

		return strtolower($word);
	}