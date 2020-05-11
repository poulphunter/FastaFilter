<?php

date_default_timezone_set('Europe/Paris');

$in='';
$out='';
$name='';
$min_length=-1;
$max_length=-1;
$uppercase=false;
$lowercase=false;
$seq_length_min=-1;
$seq_length_max=-1;
$seq_length_sum=0;
$seq_global_length_min=-1;
$seq_global_length_max=-1;
$seq_global_length_sum=0;
$check=false;
$newline_LF=false;
$removeN=false;
$remove_sequence_newline=false;
$truncTitle120=false;
$NucleicAcideCode_regexp='/[^ACGTURYKMSWBDHVN\-]/i';
$AminoAcideCode_regexp='/[^ABCDEFGHIJKLMNOPQRSTUVWXYZ\*\-]/i';

for ($i=0;$i<$argc;$i++)
{
	if ($argv[$i][0]=='-')
	{
		if (substr($argv[$i],0,6)=='-name=')
		{
			$name=substr($argv[$i],6);
			if ($name[0]=='"' || $name[0]=="'")
			{
				$name=substr($name,1,-1);
			}
		}
		if (substr($argv[$i],0,4)=='-in=')
		{
			$in=substr($argv[$i],4);
			if ($in[0]=='"' || $in[0]=="'")
			{
				$in=substr($in,1,-1);
			}
		}
		if (substr($argv[$i],0,5)=='-out=')
		{
			$out=substr($argv[$i],5);
			if ($out[0]=='"' || $out[0]=="'")
			{
				$out=substr($out,1,-1);
			}
		}
		if (substr($argv[$i],0,12)=='-min_length=')
		{
			$min_length=substr($argv[$i],12);
			if ($min_length[0]=='"' || $min_length[0]=="'")
			{
				$min_length=substr($min_length,1,-1);
			}
			$min_length=intval($min_length);
		}
		if (substr($argv[$i],0,12)=='-max_length=')
		{
			$max_length=substr($argv[$i],12);
			if ($max_length[0]=='"' || $max_length[0]=="'")
			{
				$max_length=substr($max_length,1,-1);
			}
			$max_length=intval($max_length);
		}
		if ($argv[$i]=='-uppercase')
		{
			$uppercase=true;
		}
		if ($argv[$i]=='-lowercase')
		{
			$lowercase=true;
		}
		if ($argv[$i]=='-check')
		{
			$check=true;
		}
		if ($argv[$i]=='-newline_LF')
		{
			$newline_LF=true;
		}
		if ($argv[$i]=='-removeN')
		{
			$removeN=true;
		}
		if ($argv[$i]=='-remove_sequence_newline')
		{
			$remove_sequence_newline=true;
		}
		if ($argv[$i]=='-truncTitle120')
		{
			$truncTitle120=true;
		}
	}
}

if ($in=='' || $out=='')
{
	echo "no parameters..."."\n";
	echo "-in=\"file.fasta\""."\n";
	echo "-out=\"file.fasta\""."\n";
	echo "-name=\"complete\" : search and copy only sequences with \"complete\" in the name."."\n";
	echo "-uppercase : convert sequences in uppercase"."\n";
	echo "-lowercase : convert sequences in lowercase"."\n";
	echo "-check : warning if not NucleicAcideCode and AminoAcideCode"."\n";
	echo "-newline_LF : change newlines to LF format (Unix)"."\n";
	echo "-removeN : change RNA 'N' char to '-' "."\n";
	echo "-remove_sequence_newline : remove newline in sequences"."\n";
	echo "-minlength=1000 : copy only sequences with a length upper than 1000"."\n";
	echo "-maxlength=1000 : copy only sequences with a length lower than 1000"."\n";
	exit;
}

echo 'in: "'.$in.'"'."\n";
echo 'out: "'.$out.'"'."\n";
if ($name!='')
{
	echo 'name: "'.$name.'"'."\n";
}
if ($uppercase)
{
	echo 'uppercase: true'."\n";
}
if ($lowercase)
{
	echo 'lowercase: true'."\n";
}
if ($check)
{
	echo 'check: true'."\n";
}
if ($newline_LF)
{
	echo 'newline_LF: true'."\n";
}
if ($removeN)
{
	echo 'removeN: true'."\n";
}
if ($remove_sequence_newline)
{
	echo 'remove_sequence_newline: true'."\n";
}
if ($truncTitle120)
{
	echo 'truncTitle120: true'."\n";
}
if ($min_length!=-1)
{
	echo 'min_length: '.$min_length."\n";
}
if ($max_length!=-1)
{
	echo 'max_length: '.$max_length."\n";
}
if ($max_length!=-1)
{
	echo 'max_length: '.$max_length."\n";
}
// exit;

$row = 0;
$cnt = 0;
$fp=fopen($out,'w');
$copy=true;
$sequence=[];
$sequence['name']='';
$sequence['seq']='';
$sequence['copy']=true;
function write_sequence()
{
	global $fp;
	global $sequence;
	global $seq_length_min;
	global $seq_length_max;
	global $seq_length_sum;
	global $seq_global_length_min;
	global $seq_global_length_max;
	global $seq_global_length_sum;
	global $uppercase;
	global $lowercase;
	global $min_length;
	global $max_length;
	global $name;
	global $cnt;
	global $newline_LF;
	global $removeN;
	global $remove_sequence_newline;
	global $truncTitle120;
	global $check;
	global $NucleicAcideCode_regexp;
	global $AminoAcideCode_regexp;
	if ($truncTitle120)
	{
		$sequence['name']=substr($sequence['name'],0,119);
		$sequence['name']=str_replace(";",'',$sequence['name']);
		$sequence['name']=str_replace("\\",'',$sequence['name']);
		$sequence['name']=str_replace('"','',$sequence['name']);
		$sequence['name']=str_replace('<','',$sequence['name']);
		$sequence['name']='>'.str_replace('>','',$sequence['name']);
		// echo $sequence['name']."\n";
	}
	if ($uppercase)
	{
		$sequence['seq']=strtoupper($sequence['seq']);
	}
	if ($lowercase)
	{
		$sequence['seq']=strtolower($sequence['seq']);
	}
	$seq_continuous=str_replace("\t",'',str_replace("\n",'',str_replace("\r",'',str_replace(' ','',$sequence['seq']))));
	$sequence['length']=strlen($seq_continuous);
	if ($removeN)
	{
		$seq_continuous=str_replace('N','-',$seq_continuous);
		$seq_continuous=str_replace('n','-',$seq_continuous);
	}
	if ($check)
	{
		preg_match($NucleicAcideCode_regexp,$seq_continuous,$matches);
		if (count($matches)>0)
		{
			echo "Warning : not an NucleicAcideCode : ".$sequence['name']."\n";
			var_dump($matches);
			echo "\n";
		}
		preg_match($AminoAcideCode_regexp,$seq_continuous,$matches);
		if (count($matches)>0)
		{
			echo "Warning : not an AminoAcideCode : ".$sequence['name']."\n";
			var_dump($matches);
			echo "\n";
		}
	}
	if ($seq_global_length_min==-1)
	{
		$seq_global_length_min=$sequence['length'];
	}
	if ($seq_global_length_max==-1)
	{
		$seq_global_length_max=$sequence['length'];
	}
	if ($seq_global_length_min>$sequence['length'])
	{
		$seq_global_length_min=$sequence['length'];
	}
	if ($seq_global_length_max<$sequence['length'])
	{
		$seq_global_length_max=$sequence['length'];
	}
	$seq_global_length_sum+=$sequence['length'];
	
	if ($name!='')
	{
		if (strpos(strtolower($sequence['name']),$name)>0)
		{
			$sequence['copy']=true;
		} else {
			$sequence['copy']=false;
		}
	}
	if ($max_length!=-1 && $sequence['length']>$max_length)
	{
		$sequence['copy']=false;
	}
	if ($min_length!=-1 && $sequence['length']<$min_length)
	{
		$sequence['copy']=false;
	}
	if ($sequence['copy'])
	{
		$cnt+=1;
		$fasta=$sequence['name']."\r\n".$sequence['seq'];
		if ($remove_sequence_newline)
		{
			$fasta=$sequence['name']."\r\n".$seq_continuous."\r\n";
		}
		if ($newline_LF)
		{
			$fasta=str_replace("\r\n","\n",$fasta);
			$fasta=str_replace("\r","\n",$fasta);
		}
		fwrite($fp,$fasta);
		if ($seq_length_min==-1)
		{
			$seq_length_min=$sequence['length'];
		}
		if ($seq_length_max==-1)
		{
			$seq_length_max=$sequence['length'];
		}
		if ($seq_length_min>$sequence['length'])
		{
			$seq_length_min=$sequence['length'];
		}
		if ($seq_length_max<$sequence['length'])
		{
			$seq_length_max=$sequence['length'];
		}
		$seq_length_sum+=$sequence['length'];
	}
}
if (($file=fopen($in,"r"))!==FALSE) {
  while(!feof($file)) {
        $line = fgets($file);
		if ($line[0]=='>')
		{
			$row++;
			if ($sequence['name']!='') // Sequence complete
			{
				write_sequence();
			}
			$sequence['name']=str_replace("\n",'',str_replace("\r",'',$line));
			$sequence['seq']='';
			$sequence['copy']=true;
		} else {
			if (strpos($line,'>')>0)
			{
				$lin=explode('>',$line);
				if (count($lin)>2)
				{
					echo "Warning: something really strange on line : [".$line."]\n";
					$sequence['seq']=$sequence['seq'].$line;
				} else {
					$sequence['seq']=$sequence['seq'].$lin[0];
					$row++;
					write_sequence();
					$sequence['name']=str_replace("\n",'',str_replace("\r",'','>'.$lin[1]));
					$sequence['seq']='';
					$sequence['copy']=true;
				}
			} else {
				$sequence['seq']=$sequence['seq'].$line;
			}
		}
	}
    fclose($file);
}
write_sequence();
fclose($fp);
echo 'Sequences founds: '.$row."\n";
$avg=(($row>0)?($seq_global_length_sum/$row):(0));
echo 'Sequences stats min length | max length | average: '.$seq_global_length_min.' | '.$seq_global_length_max.' | '.number_format($avg,2,'.','').' '."\n";
echo 'Sequences exported: '.$cnt."\n";

$avg=(($cnt>0)?($seq_length_sum/$cnt):(0));
echo 'Sequences exported stats min length | max length | average: '.$seq_length_min.' | '.$seq_length_max.' | '.number_format($avg,2,'.','').' '."\n";
exit;

?>
