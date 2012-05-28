<?php
	class Page{
		protected $PageMeta=array('Author'=>null, 'CurrentRevision'=>0, 'Created'=>null, 'Modified'=>null, 'Talk'=>array());
		protected $RevisionMeta=array('Title'=>null, 'Editor'=>null, 'Comment'=>'Minor edit.', 'Data'=>null);
		protected $IsNew=true, $IsDirty=false, $Revision=0, $OldName=false, $Path='/';
		//TODO: implement $HighestRevision for $AllRevs
		protected $RevList=null, $RevCache=array();

		public function __construct(){
			$this->Created=time();
			$this->Modified=time();
		}

		public function __set($k, $v){
			if(in_array($k, array('Author', 'CurrentRevision', 'Created', 'Modified')))
				$in='PageMeta';
			elseif(in_array($k, array('Title', 'Editor', 'Comment', 'Data')))
				$in='RevisionMeta';
			elseif(in_array($k, array('IsNew', 'IsDirty', 'OldName', 'Revision', 'Path')))
				$this->$k=$v;
			else
				throw new Exception("'{$k}' is not a property of this class");
			if(!$this->IsNew&&!$this->IsDirty) $this->Revision++;
			$this->IsDirty=true;
			if($k=='Title') $this->OldName=$this->Filename;
			if(isset($in)) $this->{$in}[$k]=$v;
		}

		public function __get($k){
			if(isset($this->PageMeta[$k])) return $this->PageMeta[$k];
			if(isset($this->RevisionMeta[$k])) return $this->RevisionMeta[$k];
			switch($k){
				case 'Filename':
					return preg_replace('#[^A-Za-z0-9_-]#', '_', $this->Title);
				case 'Path':
					return $this->Path;
				case 'URL':
					return sprintf('/%s/%s%s%s', $this->Path, $this->Filename, URL_SUFFIX?'.':'', URL_SUFFIX?:'');
			}
			return null;
		}
		
		private function _load($page, $revision=null){
			if(is_dir(DATA_DIR.'/'.$page)) $page.='/index'.(URL_SUFFIX?'.'.URL_SUFFIX:'');
			if(URL_SUFFIX) $page=preg_replace('#\.'.URL_SUFFIX.'$#', '', $page);
			$page_parts=explode('/', $page);
			$file=array_pop($page_parts);
			$dir=DATA_DIR.implode('/', $page_parts).'/';
			$this->Path=trim(implode('/', $page_parts), '/');
			$metafile=$dir.'META-'.$file.'.json';
			if(!file_exists($metafile)) throw new Exception("Missing metadata '{$metafile}' for page {$page}", 404);
			$metadata=json_decode(file_get_contents($metafile), true);
			if(!$revision) $revision=$metadata['CurrentRevision'];
			$rfile=sprintf('%s/REV-%s-%d.json', $dir, $file, $revision);
			if(!file_exists($rfile)) throw new Exception("No file for page {$page} for revision {$revision} (Expected: {$rfile})", 404);
			$this->PageMeta=$metadata;
			$this->RevisionMeta=json_decode(file_get_contents($rfile), true);
			$this->Revision=$this->CurrentRevision;
			$this->IsNew=false;
			$this->IsDirty=false;
		}

		public static function load($page, $revision=null){
			$p_obj=new Page();
			$p_obj->_load($page, $revision);
			return $p_obj;
		}

		public function revisionList(){
			if($this->RevList===null){
				$this->RevList=array();
				$high_rev=isset($this->HighestRevision)?$this->HighestRevision:$this->CurrentRevision;
				for($i=$high_rev; $i>=0; $i--){
					$rd=json_decode(file_get_contents(sprintf("%s/%s/REV-%s-%d.json", DATA_DIR, $this->Path, $this->Filename, $i)), true);
					$this->RevList[$i]=array($rd['Editor'], $rd['Comment']);
				}
			}
			return $this->RevList;
		}

		public function revision($rid){
			if(!isset($this->RevCache[$rid])){
				$this->RevCache[$rid]=json_decode(file_get_contents(sprintf("%s/%s/REV-%s-%d.json", DATA_DIR, $this->Path, $this->Filename, $rid)));
			}
			return $this->RevCache[$rid];
		}

		public function talk($user, $message){
			$talk=$this->Talk;
			$talk[]=array($user, $message);
			$this->Talk=$talk;
		}

		public function save(){
			$this->RevList=null; //force this to be reloaded after a save
			$this->RevCache=array(); //this too
			if($this->OldName){
				foreach(glob(sprintf('%s/%s/*%s*.json', DATA_DIR, $this->Path, $this->OldName)) as $oldfile){
					if(!preg_match('#(META|REV)-'.$this->OldName.'(-\d)?\.json$#', $oldfile)) continue;
					$newfile=str_replace($this->OldName, $this->Filename, $oldfile);
					rename($oldfile, $newfile);
				}
			}
			$dir=sprintf('%s/%s/', DATA_DIR, $this->Path);
			$metafile=sprintf('%sMETA-%s.json',$dir, $this->Filename);
			$revfile=sprintf('%sREV-%s-%d.json', $dir, $this->Filename, $this->Revision);
			if(file_exists($revfile)) throw new Exception("Attempt to overwrite existing revision: {$revfile}");
			$this->CurrentRevision=$this->Revision;
			$this->Modified=time();
			if(!file_exists($dir)) mkdir($dir, 0777, true);
			file_put_contents($metafile, json_encode($this->PageMeta));
			file_put_contents($revfile, json_encode($this->RevisionMeta));

			$this->IsNew=false;
			$this->IsDirty=false;
			return true;
		}
	}