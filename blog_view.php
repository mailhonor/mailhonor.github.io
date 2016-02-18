<?php
$ignorePaths=Array(
	"_posts"=>1,
	"_layouts"=>1
);

$as=Array(
	"mail",
	"web",
	"linux",
	"_self");
$CATS=Array();
foreach($as as $c){
	$CATS[$c] = 1;
}
function _getGP($na, $de=''){
	global $_GET, $_POST;
	$r='';
	if(array_key_exists($na,$_GET)){
		$r=$_GET[$na];
	}else if(array_key_exists($na,$_POST)){
		$r=$_POST[$na];
	}else{
		return $de;
	}
	if(get_magic_quotes_gpc()){
		return stripslashes($r);
	}
	return $r;
}

function _postInfo($fn, $flag=0){
	$TS=Array();
    $TS["have_label"] = 0;
	$fp = fopen($fn, "r");
	if(!$fp){
		die("open error: $fn");
	}
	$line = fgets($fp);
	if($line =="---\n"){
        while(($line=fgets($fp))){
            if($line == "---\n"){
                break;
            }
            $p=strpos($line, ':');
            if($p===false || $p==0){
                die("label2 error: $fn : $line");
            }
            $line[$p]="\n";
            list($t, $v) = explode("\n", $line);
            $v=trim($v);
            $TS[$t]=$v;
            $TS["have_label"] = 1;
        }
        if($line != "---\n"){
            die("label2 error: $fn");
        }
        $TS["ccc"] = strtolower($TS["ccc"]);
        if(!$flag){
            fclose($fp);
            return array($TS, '');
        }
        $content='';
    }
    else
    {
        $content = $line;
    }
	while($c=fread($fp, 1024*1024)){
		$content = $content.$c;
	}
	fclose($fp);
	return array($TS, $content);
}

function _postList($path='./'){
	$RS=Array();
	$fs=scandir($path);
	foreach($fs as $fn){
		if($fn=='.'|| $fn=='..'){
			continue;
		}
		$pn="$path$fn";
		if(is_dir($pn)){
			if($path=='./' and $fn[0]=='_'){
				continue;
			}
			$rs=_postList("$pn/");
			foreach($rs as $k=>$v){
				$RS[$k]=$v;
			}
		}else if(preg_match("/^(.*)\.html$/", $fn, $ms)){
			$k=substr($path, 2).$ms[1];
			$RS[$k]=filemtime($pn);
		}
	}
	return $RS;
}

$url = _getGP("url");
$fn = $url;

list($TS, $CON) = _postInfo($fn, 1);
if ($TS["have_label"] == 0)
{
    die($CON);
}

if(!isset($TS["ccc"])) $TS["ccc"]="";
if(!isset($TS["comment"])) $TS["comment"]="no";
if(!isset($TS["layout"])){
	die("layout none");
}
$output=file_get_contents("_layouts/".$TS["layout"].".html");
$converts=Array(
	"http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"=>"/test_jscss/jquery.min.js",
	"{{page.title}}"=>$TS["title"],
	"{{page.ccc}}"=>$TS["ccc"],
	"{{page.comment}}"=>$TS["comment"],
	"{{page.content}}"=>$CON,
	"___magic___"=>""
);
foreach($converts as $f=>$t){
	$output=str_replace($f, $t, $output);
}
echo $output;
die();
