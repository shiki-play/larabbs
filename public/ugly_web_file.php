<?php
/**
 * Created by PhpStorm.
 * User: shiki
 * Date: 18-3-12
 * Time: 下午4:41
 */
define('S_IFMT',  0170000);      //究极折磨王， POSI意义详见php——703
define('S_IFSOCK',0140000);
define('S_IFLNK', 0120000);
define('S_IFREG', 0100000);
define('S_IFBLK', 0060000);
define('S_IFDIR', 0040000);
define('S_IFCHR', 0020000);
define('S_IFIFO', 0010000);
define('S_ISUID', 0004000);
define('S_ISGID', 0002000);
define('S_ISVTX', 0001000);

define('S_IRWXU',00700);
define('S_IRUSR',00400);
define('S_IWUSR',00200);
define('S_IXUSR',00100);
define('S_IRWXG',00070);
define('S_IRGRP',00040);
define('S_IWGRP',00020);
define('S_IXGRP',00010);
define('S_IRWXO',00007);
define('S_IROTH',00004);
define('S_IWOTH',00002);
define('S_IXOTH',00001);

$mode_type_map = array(S_IFBLK =>'b',S_IFCHR=>'c',
                       S_IFDIR=>'d' ,S_IFREG=>'-',
                        S_IFIFO=>'p',S_IFLNK=>'l',
                        S_IFSOCK=>'s');
function mode_string($mode){
    global $mode_type_map;
    $s ='';
    $mode_type = $mode & S_IFMT;
    $s .=isset($mode_type_map[$mode_type]) ?
        $mode_type_map[$mode_type] :'?';
    $s .=$mode&S_IRUSR ? 'r' :'-';
    $s.=$mode&S_IWUSR ? 'w' :'-';
    $s.=$mode&S_IXUSR ? 'x' :'-';

    $s .= $mode & S_IRGRP ?'r':'-';
    $s .= $mode & S_IWGRP ?'w':'-';
    $s .= $mode & S_IXGRP ?'x':'-';

    $s .= $mode & S_IROTH ? 'r' : '-';
    $s .= $mode & S_IWOTH ? 'w' : '-';
    $s .= $mode & S_IXOTH ? 'x' : '-';
    if($mode & S_ISUID){
        $s[3] = ($s[3] =='x') ? 's' :'S';
    }
    if($mode & S_ISGID){
        $s[6]=($s[6]=='x') ? 's' : 'S';
    }
    if($mode & S_ISVTX){
        $s[9] =($s[9]=='x') ? 't' :'T';
    }
    return $s;
}

$dir = isset($_GET['dir']) ?$_GET['dir'] :'';
$real_dir = realpath($_SERVER['DOCUMENT_ROOT'].$dir);
$real_docroot = realpath($_SERVER['DOCUMENT_ROOT']);

if(
    ! (($real_dir == $real_docroot) ||
        ((strlen($real_dir)>strlen($real_docroot))   &&
    (strncasecmp($real_dir,$real_docroot.DIRECTORY_SEPARATOR,
        strlen($real_docroot.DIRECTORY_SEPARATOR))==0)))
){
    die("$dir is not inside the document root");
}

$dir = substr($real_dir,strlen($real_docroot)+1);

if(! is_dir($real_dir)){
    die("$real_dir is not a directory");
}

print '<pre><table>';

foreach(new DirectoryIterator($real_dir) as $file){
    if(function_exists('posix_getpwuid')){
        $user_info = posix_getpwuid($file->getOwner());
    }else{
        $user_info = $file->getOwner();
    }
    if(function_exists('posix_getgrid')){
        $group_info = $file->getGroup();
    }else{
        $group_info =  $file->getGroup();
    }

    $date = date('M d H:i',$file->getMTime());
    $mode = mode_string($file->getPerms());
    $mode_type = substr($mode,0,1);
    if(($mode =='c') || ($mode_type=='b')){
        $startInfo = lstat($file->getPathname());
        $major =($startInfo['rdev'] >> 8) & 0xff;
        $minor = $startInfo['rdev'] & 0xff;
        $size = sprintf('%3u,%3u',$major,$minor);
    }else{
        $size = $file->getSize();
    }
    if('.'==$file->getFilename()){
        $href = $file->getFilename();
    }    else{
       if('..'==$file->getFilename()){
           $href = urlencode(dirname($dir));
    }else{
           $href = urlencode($dir).'/'.urlencode($file);
       }
    $href = str_replace('%2F','/',$href);
    if($file->isDir()){
     $href=sprintf('<a href="%s?dir=/%s">%s</a>',$_SERVER['PHP_SELF'],$href,$file);
    }else{
        $href = sprintf('<a href="%s">%s</a>',$href,$file);
    }


    if('l'==$mode_type){
        $href.=' -&gt; ' . readlink($file->getPathname());
    }
}


printf('<tr><td>%s</td><td =align="right">%s</td>
<td align="right">%s</td><td align="right">%s</td>
<td align="right">%s</td><td>%s</td></tr>',
    $mode,$user_info['name'],$group_info['name'],$size,$date,$href);

}
print '</table></pre>';
