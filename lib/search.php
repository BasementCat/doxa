<?php
	$_SEARCH_DEFAULT_TAG_WEIGHT=1;
	$_SEARCH_TAG_WEIGHT=array('h1'=>2, 'h2'=>1.6, 'h3'=>1.3, 'strong'=>1.2, 'em'=>1.2);
	$_SEARCH_DIST_WEIGHT=-1;

	function search_analyze($rootNode){
		global $_SEARCH_DEFAULT_TAG_WEIGHT, $_SEARCH_TAG_WEIGHT, $_SEARCH_DIST_WEIGHT;
		/*For each word in the text contained within the provided node (which should
		 * really be the root of the document), we want to do several things.  The
		 * first is to find the stem of the word for searching.  Second, determine the
		 * distance from the beginning of the document.  Third, invert the distance
		 * (so that the earliest words have the highest number), multiply that by
		 * the distance weight, then multiply the result by the the tag weight.
		 */
		$out=array();
		$overallDistance=0;
		foreach($rootNode->getAll('_') as $textNode){
			//Find the nearest parent tag that we have a weight for
			$tempNode=$textNode;
			while($tempNode->Tag&&!isset($_SEARCH_TAG_WEIGHT[$tempNode->Tag]))
				$tempNode=$tempNode->Parent;
			if(!$tempNode->Tag) //root node
				$tagWeight=$_SEARCH_DEFAULT_TAG_WEIGHT;
			else //we found a node that we have a weight for
				$tagWeight=$_SEARCH_TAG_WEIGHT[$tempNode->Tag];

			//get the stem & the distance for each word, and add it to the list
			$currentDistance=0;
			foreach(preg_split("#\W+#", strtolower($textNode->Text)) as $word){
				$word=trim($word);
				if(!$word) continue;
				$wordStem=stem_porter2($word);
				$wordDistance=($overallDistance+$currentDistance);
				$currentDistance++;
				$out[]=array($word, $wordStem, $wordDistance, $tagWeight);
			}
			$overallDistance+=$currentDistance;
		}
		//now that we've got our final distance we can go through the list again
		//and calculate the final weight
		$old_out=$out;
		$out=array();
		$dist_weight=$_SEARCH_DIST_WEIGHT;
		if($dist_weight<0) $dist_weight=1/$overallDistance;
		foreach($old_out as $word_info){
			list($word, $wordStem, $wordDistance, $tagWeight)=$word_info;
			$inverseDistance=$overallDistance-$wordDistance;
			//if dist_weight is set to 0, then the distance is ignored
			$finalWeight=$tagWeight*($dist_weight==0?1:($dist_weight*$inverseDistance));
			$out['SumWeights'][$word]+=$finalWeight;
			$out['Index'][]=array(
				'Word'=>$word,
				'Stem'=>$wordStem,
				'Distance'=>$wordDistance,
				'IDistance'=>$inverseDistance,
				'TagWeight'=>$tagWeight,
				'Weight'=>$finalWeight
				);
		}
		return $out;
	}

	function search_get_phrase($text){
		/*This will be used both for generating search phrases from the page text
		 * and for turning user-supplied search phrases into something to compare
		 * against the index
		 */
		return trim(preg_replace('#\s+#', ' ', implode(' ', preg_split('#\W*?\s+\W*?#', ' '.$text.' '))));
	}

	function search_build_index($rootNode){
		$out="FULLTEXT\n%s\nSUMWEIGHTS %d\n%s\nWORDS %d\n%s";
		$fullText=search_get_phrase($rootNode->asText());
		$index=search_analyze($rootNode);
		$sumWeights=array();
		foreach($index['SumWeights'] as $word=>$weight)
			$sumWeights[]=sprintf("%14.5f\t%s", $weight, $word);
		$words=array();
		foreach($index['Index'] as $wi){
			$words[]=sprintf("%14d\t%14d\t%14.5f\t%14.5f\t%-24s\t%-24s",
				$wi['Distance'], $wi['IDistance'], $wi['Weight'], $wi['TagWeight'],
				$wi['Word'], $wi['Stem']);
		}
		return sprintf($out, $fullText, count($sumWeights), implode("\n", $sumWeights),
			count($words), implode("\n", $words));
	}