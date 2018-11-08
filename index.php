<?php
session_start();

// my code for ban begin

$client_ip = ( !empty($HTTP_SERVER_VARS['REMOTE_ADDR']) ) ? $HTTP_SERVER_VARS['REMOTE_ADDR'] : ( ( !empty($HTTP_ENV_VARS['REMOTE_ADDR']) ) ? $HTTP_ENV_VARS['REMOTE_ADDR'] : getenv('REMOTE_ADDR') );

if (stripos ($_SERVER['REQUEST_URI'], '.htm')==0
    && stripos ($_SERVER['REQUEST_URI'], '.gif')==0)
{
  if ($fp = @fopen("./connects.txt", 'ab'))
  {
    fwrite($fp, Date("Y-m-d H:i:s").' '. $_SERVER['REQUEST_URI'].' '.$client_ip."\n");
    fclose($fp);
  }
}

if(stristr($_SERVER['REQUEST_URI'],'/?')) 
{
	$str_url=str_replace('/?', '/index.php?', $_SERVER['REQUEST_URI']);
	header("HTTP/1.1 301 Moved Permanently");
	header('Location: http://dveri-legion.com.ua'.$str_url);	
	exit;
}

//Путь
$domain_path="./";
$absolute_link='http://dveri-legion.com.ua/';
$www="web";
mb_internal_encoding("utf8");
//Подключение классов
require_once($domain_path."cfg/connect.php");//локальные настройки базы данных
require_once($domain_path."functions/functions.php");//общие функции
require_once($domain_path."class/db_mysql.php");//class для работы с базой данных
require_once($domain_path."class/category.php");//class для работы с категориями
require_once($domain_path."class/objects.php");//class для работы с объектами

//Соединение с БД
$db = new db(DB_HOST, $DB_USER, $DB_PASSWORD, DB_BASE_NAME);
$sql = 'SET NAMES utf8';
$db->Execute($sql);

if($_GET["o"]>0)
{
	$obj=$db->getSelect("SELECT category.view_id, category.category_id FROM object LEFT JOIN category ON object.category_id=category.category_id WHERE object.object_id='".intval($_GET["o"])."' AND object.object_visible=1 AND category.category_visible=1");
	if(($obj["view_id"][0]==1) AND ($obj["category_id"][0]!=55) AND ($obj["category_id"][0]!=25) AND ($obj["category_id"][0]!=57) AND ($obj["category_id"][0]!=27) AND ($obj["category_id"][0]!=53))
	{	
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: http://'.$_SERVER['SERVER_NAME'].'/index.php?c='.$obj["category_id"][0]);  
		exit;
	}
	
	if($obj["category_id"][0]==29)
	{
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: http://'.$_SERVER['SERVER_NAME'].'/index.php?c='.$obj["category_id"][0]);
		exit;
	}
	
	$object_redirect=array(626,627,628,630,631,632,633,566,571,572,573,574,575,576,577,579,580,543,544,545,546,547,548,549,550,551,552,553,554,555,556,557,559,560,562,563,564,565,567,568,569,570,1242,1243,1244,1246,1250,1251,1252,1569,1571,1572,1573,1574,1576,1577,1578,1582,1583,1584,1585,1589,1592,1593,1595,1597,1598,1599,1601,1602,1603,1604,1605,1606,1607,1608,1523,1524,1525,1526,1527,1528,1529,1530,1531,1532,1533,1534,1535,1536,1537,1538,1539,1540,1541,1542);

	if(array_search($_GET["o"], $object_redirect)) 
	{
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: http://'.$_SERVER['SERVER_NAME'].'/index.php?c='.$obj["category_id"][0]);	
		exit;
	}
}

//Получение параметров  GET, POST
getParams(1); // id object страница по умолчанию (главная)

//Смена языка системы
if ($a_params["l"]!="") $_SESSION["lang_id"]=$a_params["l"];
if (!isset($_SESSION["lang_id"])) { $lang_id=1; } else { $lang_id=$_SESSION["lang_id"]; }

if ($lang_id==1) $lang_name="ru";
if ($lang_id==2) $lang_name="uk";
if ($lang_id==3) $lang_name="en";

//Категория
$category = new category('category', 'category', $db);

//Объект
$objects = new objects($db, $a_params, $lang_id);


$lang_mass=$objects->getSelect("SELECT * FROM lang"); 

//Дерево объектов
if ($a_params["o"]!="") $base_object_category_id=$objects->getTree("object", $a_params["o"]);

//Дерево категорий
if ($a_params["c"]=="") $category->Parents($base_object_category_id, null);
else
{
    $category->Parents($a_params["c"], null);
}

//Вывод дерева категорий
$stroka='';
$str_where=array();
while ($item = $category->NextRow())
{
    $category_mass["category_id"][]=$item["category_id"];
    $category_mass["category_name"][]=$item["category_name"];
    $category_mass["category_parent"][]=$item["category_parent"];
    $category_mass["view_id"][]=$item["view_id"];
    $category_mass["category_path"][]=$item["category_path"];
    $category_mass["category_visible"][]=$item["category_visible"]; 
    $category_mass["category_subdomain"][]=$item["category_subdomain"];
    $category_mass["category_picture_icon"][]=$item["category_picture_icon"];
    $category_mass["category_picture_banner"][]=$item["category_picture_banner"];
    $category_mass["category_picture_background"][]=$item["category_picture_background"];
    $category_mass["category_picture_admin"][]=$item["category_picture_admin"];
    $str_where[]=$item["category_id"];

    $o_category_name = $objects->getCategoryName($item["category_id"]);
    if (isset($o_category_name["category_name_name"][0])) $category_mass["category_name_name"][]=$o_category_name["category_name_name"][0];
    else $category_mass["category_name_name"][]=null;
    if (isset($o_category_name["category_name_title"][0])) $category_mass["category_name_title"][]=$o_category_name["category_name_title"][0];
    else $category_mass["category_name_title"][]=null;
    if (isset($o_category_name["category_name_keywords"][0])) $category_mass["category_name_keywords"][]=$o_category_name["category_name_keywords"][0];
    else $category_mass["category_name_keywords"][]=null;
    if (isset($o_category_name["category_name_descriptions"][0])) $category_mass["category_name_descriptions"][]=$o_category_name["category_name_descriptions"][0];
    else $category_mass["category_name_descriptions"][]=null;
    if (isset($o_category_name["category_name_description"][0])) $category_mass["category_name_description"][]=$o_category_name["category_name_description"][0];
    else $category_mass["category_name_description"][]=null;
	if (isset($o_category_name["category_name_description_2"][0])) $category_mass["category_name_description_2"][]=$o_category_name["category_name_description_2"][0];
    else $category_mass["category_name_description_2"][]=null;
}
//Получение шаблонов и контейнера для категорий
if (count($str_where)>0)
{
    $is_repeat=false;
    $is_repeat_while=false;
    $id_repeat=null;
    $n=0;
    for($s=(count($str_where)-1);$s>=0;$s--)
    { 
        if ($n>0) $is_repeat_while=true;
              
        $res = $db->Execute("SELECT category.category_id, shablon.shablon_id, shablon.shablon_file, shablon_group.shablon_group_id, category_shablon_link.category_shablon_link_id
        FROM (((category LEFT JOIN category_shablon_link ON category.category_id=category_shablon_link.category_id)
        LEFT JOIN category_shablon ON category_shablon_link.category_shablon_id=category_shablon.category_shablon_id)
        LEFT JOIN shablon ON category_shablon.shablon_id=shablon.shablon_id)
        LEFT JOIN shablon_group ON shablon.shablon_group_id=shablon_group.shablon_group_id
        WHERE shablon.shablon_id>0 AND category.category_id=".$str_where[$s]."
        ORDER BY category_shablon_link.category_shablon_link_sort;");    
        
        if ($res->RecordCount()>0)
        {
            $n=0;            
            while ($category_element = $res->FetchArray())
            {
                //Контейнер
                if ($category_element["shablon_group_id"]==1)
                {
                    $conteyner_mass["category_id"][]=$category_element["category_id"];
                    $conteyner_mass["shablon_id"][]=$category_element["shablon_id"];
                    $conteyner_mass["shablon_file"][]=$category_element["shablon_file"];
                    $conteyner_mass["shablon_group_id"][]=$category_element["shablon_group_id"];
                    $conteyner_mass["category_shablon_link_id"][]=$category_element["category_shablon_link_id"];

                    $conteyner_mass["shablon_type"][]=0;
                    $conteyner_mass["shablon_link_id"][]=$category_element["category_shablon_link_id"];
                }

                if ($is_repeat_while==false)
                {
                    //Шаблоны
                    if (($is_repeat==false) AND ($category_element["shablon_group_id"]!=1))
                    {
                        $id_repeat=$category_element["category_id"];
                        $is_repeat=true;
                    }

                    $current_repeat=$category_element["category_id"];

                    if ($id_repeat==$current_repeat)
                    {
                        $shablon_mass["category_id"][]=$category_element["category_id"];
                        $shablon_mass["shablon_id"][]=$category_element["shablon_id"];
                        $shablon_mass["shablon_file"][]=$category_element["shablon_file"];
                        $shablon_mass["shablon_group_id"][]=$category_element["shablon_group_id"];

                        $shablon_mass["shablon_type"][]=0;
                        $shablon_mass["shablon_link_id"][]=$category_element["category_shablon_link_id"];                

                        //Явные шаблоны
                        if ((is_numeric($a_params["s"])) AND ($a_params["n"]==$n))
                        {
                            $t_res = $db->Execute("SELECT *
                            FROM shablon
                            WHERE shablon.shablon_id=".$a_params["s"].";");

                            if ($t_res->RecordCount()>0)
                            {
                                $tmp_shablon = $t_res->FetchArray();
                                $shablon_mass["category_id"][]=$tmp_shablon["category_id"];
                                $shablon_mass["shablon_id"][]=$tmp_shablon["shablon_id"];
                                $shablon_mass["shablon_file"][]=$tmp_shablon["shablon_file"];
                                $shablon_mass["shablon_group_id"][]=$tmp_shablon["shablon_group_id"];

                                $shablon_mass["shablon_type"][]=0;
                                $shablon_mass["shablon_link_id"][]=$category_element["category_shablon_link_id"];
                            }
                        }
                        $n++;
                    }
                }
            }
        }       
    }
}


//Вывод дерева объектов
$object_mass = array();
$str_where='';
if (count($objects->object_tree)>0)
{
    for($i=count($objects->object_tree["object_name"]);$i>=0;$i--)
    {
        if ((isset($objects->object_tree["object_id"][$i])) AND ($objects->object_tree["object_id"][$i]!=""))
        {
            $object_mass["object_id"][]=$objects->object_tree["object_id"][$i];
            $object_mass["object_name"][]=$objects->object_tree["object_name"][$i];
            $object_mass["object_parent"][]=$objects->object_tree["object_parent"][$i];
            $object_mass["category_id"][]=$objects->object_tree["category_id"][$i];
            $object_mass["view_id"][]=$objects->object_tree["view_id"][$i];
            $object_mass["object_filename"][]=$objects->object_tree["object_filename"][$i];
            $object_mass["object_picture_icon"][]=$objects->object_tree["object_picture_icon"][$i];
            $object_mass["object_picture_banner"][]=$objects->object_tree["object_picture_banner"][$i];
            $object_mass["object_picture_background"][]=$objects->object_tree["object_picture_background"][$i];
            $str_where=$str_where.'object.object_id='.$objects->object_tree["object_id"][$i].' OR ';

            $o_object_name = $objects->getObjectName($objects->object_tree["object_id"][$i]);
            if (isset($o_object_name["object_name_name"][0])) $object_mass["object_name_name"][]=$o_object_name["object_name_name"][0];
            else $object_mass["object_name_name"][]=null;
            if (isset($o_object_name["object_name_title"][0])) $object_mass["object_name_title"][]=$o_object_name["object_name_title"][0];
            else $object_mass["object_name_title"][]=null;
            if (isset($o_object_name["object_name_keywords"][0])) $object_mass["object_name_keywords"][]=$o_object_name["object_name_keywords"][0];
            else $object_mass["object_name_keywords"][]=null;
            if (isset($o_object_name["object_name_descriptions"][0])) $object_mass["object_name_descriptions"][]=$o_object_name["object_name_descriptions"][0];
            else $object_mass["object_name_descriptions"][]=null;
        }
    }
}

//Получение шаблонов и контейнера для объектов
$str_where = substr($str_where,0,strlen($stroka)-3);

if (trim($str_where)!="")
{
    $res = $db->Execute("SELECT object.object_id, shablon.shablon_id, shablon.shablon_file, shablon_group.shablon_group_id, object_shablon_link.object_shablon_link_id
    FROM (((object LEFT JOIN object_shablon_link ON object.object_id=object_shablon_link.object_id)
    LEFT JOIN object_shablon ON object_shablon_link.object_shablon_id=object_shablon.object_shablon_id)
    LEFT JOIN shablon ON object_shablon.shablon_id=shablon.shablon_id)
    LEFT JOIN shablon_group ON shablon.shablon_group_id=shablon_group.shablon_group_id
    WHERE shablon.shablon_id>0 AND (".$str_where.")
    ORDER BY object.object_parent desc, object_shablon_link.object_shablon_link_sort;");

    if ($res->RecordCount()>0)
    {
        $is_repeat=false;
        while ($object_element = $res->FetchArray())
        {
            //Контейнер
            if ($object_element["shablon_group_id"]==1)
            {
                unset ($conteyner_mass);
                $conteyner_mass["object_id"][]=$object_element["object_id"];
                $conteyner_mass["shablon_id"][]=$object_element["shablon_id"];
                $conteyner_mass["shablon_file"][]=$object_element["shablon_file"];
                $conteyner_mass["shablon_group_id"][]=$object_element["shablon_group_id"];

                $conteyner_mass["shablon_type"][]=1;
                $conteyner_mass["shablon_link_id"][]=$object_element["object_shablon_link_id"];
            }

            //Шаблоны
            if (($is_repeat==false) AND ($object_element["shablon_group_id"]!=1))
            {
                unset ($shablon_mass);
                $id_repeat=$object_element["object_id"];
                $is_repeat=true;
            }

            $current_repeat=$object_element["object_id"];

            if ($id_repeat==$current_repeat)
            {
                $shablon_mass["object_id"][]=$object_element["object_id"];
                $shablon_mass["shablon_id"][]=$object_element["shablon_id"];
                $shablon_mass["shablon_file"][]=$object_element["shablon_file"];
                $shablon_mass["shablon_group_id"][]=$object_element["shablon_group_id"];

                $shablon_mass["shablon_type"][]=1;
                $shablon_mass["shablon_link_id"][]=$object_element["object_shablon_link_id"];
            }
        }
    }
}

//Текущая категория
$category_id=$category_mass["category_id"][count($category_mass["category_id"])-1];
$category_name=$category_mass["category_name"][count($category_mass["category_id"])-1];
$category_parent=$category_mass["category_parent"][count($category_mass["category_id"])-1];
$category_view_id=$category_mass["view_id"][count($category_mass["view_id"])-1];
$category_path=$category_mass["category_path"][count($category_mass["category_path"])-1];
$category_path=$category_mass["category_visible"][count($category_mass["category_visible"])-1];
$category_subdomain=$category_mass["category_subdomain"][count($category_mass["category_subdomain"])-1];
$category_picture_icon=$category_mass["category_picture_icon"][count($category_mass["category_picture_icon"])-1];
$category_picture_banner=$category_mass["category_picture_banner"][count($category_mass["category_picture_banner"])-1];
$category_picture_background=$category_mass["category_picture_background"][count($category_mass["category_picture_background"])-1];
$category_picture_admin=$category_mass["category_picture_admin"][count($category_mass["category_picture_admin"])-1];

$category_name_name=$category_mass["category_name_name"][count($category_mass["category_name_name"])-1];
$category_name_title=$category_mass["category_name_title"][count($category_mass["category_name_title"])-1];
$category_name_keywords=$category_mass["category_name_keywords"][count($category_mass["category_name_keywords"])-1];
$category_name_descriptions=$category_mass["category_name_descriptions"][count($category_mass["category_name_descriptions"])-1];
$category_name_description=$category_mass["category_name_description"][count($category_mass["category_name_description"])-1];
$category_name_description_2=$category_mass["category_name_description_2"][count($category_mass["category_name_description_2"])-1];

//Текущий объект
if (isset($object_mass["object_id"][0]))
{
    $object_id=$object_mass["object_id"][count($object_mass["object_id"])-1];
    $object_name=$object_mass["object_name"][count($object_mass["object_id"])-1];
    $object_parent=$object_mass["object_parent"][count($object_mass["object_id"])-1];
    $object_category_id=$object_mass["category_id"][count($object_mass["category_id"])-1];
    $object_view_id=$object_mass["view_id"][count($object_mass["view_id"])-1];
    $object_filename=$object_mass["object_filename"][count($object_mass["object_filename"])-1];
    $object_picture_icon=$object_mass["object_picture_icon"][count($object_mass["object_picture_icon"])-1];
    $object_picture_banner=$object_mass["object_picture_banner"][count($object_mass["object_picture_banner"])-1];
    $object_picture_background=$object_mass["object_picture_background"][count($object_mass["object_picture_background"])-1];
    $object_name_name=$object_mass["object_name_name"][count($object_mass["object_name_name"])-1];
	$object_name_title=$object_mass["object_name_title"][count($object_mass["object_name_title"])-1];
    $object_name_keywords=$object_mass["object_name_keywords"][count($object_mass["object_name_keywords"])-1];
    $object_name_descriptions=$object_mass["object_name_descriptions"][count($object_mass["object_name_descriptions"])-1];
	
    $category_name_title=$object_mass["object_name_title"][count($object_mass["object_name_title"])-1];
    $category_name_keywords=$object_mass["object_name_keywords"][count($object_mass["object_name_keywords"])-1];
    $category_name_descriptions=$object_mass["object_name_descriptions"][count($object_mass["object_name_descriptions"])-1];
}
$category_name_title=str_replace('"','',$category_name_title);
$category_name_keywords=str_replace('"','',$category_name_keywords);
$category_name_descriptions=str_replace('"','',$category_name_descriptions);

require_once('class/property.php');
$property = new property($db, $a_params);
$o_object_property=$property->getObjectProperty(2);
$val=1;
if (isset($o_object_property["object_property_id"]))
{
    for ($d=0;$d<count($o_object_property["object_property_id"]);$d++)
    {
      if ($o_object_property["object_property_name"][$d]=="грн.") 
      { 
        $grn=$o_object_property["object_property_value"][$d]; 
        $grn_b=$o_object_property["object_property_value1"][$d];
      }
      if ($o_object_property["object_property_name"][$d]=="у.е.") 
      { 
        $dol=$o_object_property["object_property_value"][$d]; 
        $dol_b=$o_object_property["object_property_value1"][$d]; 
      }
      if ($o_object_property["object_property_name"][$d]=="евро.") 
      { 
        $evr=$o_object_property["object_property_value"][$d]; 
        $evr_b=$o_object_property["object_property_value1"][$d]; 
      }
    }
}

$is_tree = 1;
//Подключение контейнера
if ((isset($conteyner_mass["shablon_file"])) AND ((!($a_params["o"]>0)) AND (count($conteyner_mass["shablon_file"])>1)) AND ($category_view_id==4))
{
    $is_tree = 1;
    include($domain_path.'shablon/'.$www.$conteyner_mass["shablon_file"][1]);
}
else
{
/*    if ((isset($conteyner_mass["shablon_file"])) AND (count($conteyner_mass["shablon_file"]<=1))) $is_tree = 1;
    else $is_tree = 0;
    if (isset($conteyner_mass["shablon_file"][0])) include($domain_path.'shablon/'.$www.$conteyner_mass["shablon_file"][0]);*/
    if ((isset($conteyner_mass["shablon_file"])) AND (count($conteyner_mass["shablon_file"]<=1))) $is_tree = 1;
    else $is_tree = 0;
    if (isset($conteyner_mass["shablon_file"][0]))
    {
        ob_start();
        include $domain_path.'shablon/'.$www.$conteyner_mass["shablon_file"][0];
        $contents = ob_get_contents();
        ob_end_clean();
        //Проверка всех параметров
        if (check_params())
        {        
            echo $contents; 
        }
        else
        {
           //echo "error";
            $URL_404=".404.php";
            header ("Location: $URL_404");
        }
    }
}
//Проверка параметров: c,o,l,start,s,a
function check_params()
{
    global $a_params;
    global $category_id;
    global $object_id;
    global $lang_mass;    
    
    $status=false;
    if(url_check())
    {
        if((($a_params["c"]>0) AND ($category_id>0)) OR (($a_params["o"]>0) AND ($object_id>0))) $status=true;
        if((trim($a_params["l"])!='') AND (array_search($a_params["l"], $lang_mass["lang_id"])===false)) $status=false;
        if((trim($a_params["start"])!='') AND (is_numeric($a_params["start"])<>1)) $status=false;
        //if((trim($a_params["a"])!='') AND ($a_params["a"]!="complete")) $status=false;
        //if((trim($a_params["s"])!='') AND ($a_params["s"]!="complete")) $status=false;
        //При необходимости отключить проверку "a","s" для всех страниц, оставить только для 1-й страницы
        if((trim($a_params["a"])!='') AND ($a_params["a"]!="complete") AND ($category_id==1)) $status=false;
        if((trim($a_params["s"])!='') AND ($a_params["s"]!="complete") AND ($category_id==1)) $status=false;
		if($a_params["s"]=="sendmail") $status=true;
    }
    return $status;
}

//Сверка массива $a_params с REQUEST_URI
function url_check() 
{
    global $a_params;
    
    $path = parse_url($_SERVER['REQUEST_URI']);
    if (isset($path['query'])) $parameters=explode("&",$path['query']);
    for($i=0;$i<count($parameters);$i++)
    {
        $a_mass=split("=",$parameters[$i]);        
        if(!array_key_exists($a_mass[0], $a_params)) return false;
    }
    return true;
}
$db->Close();   
?>