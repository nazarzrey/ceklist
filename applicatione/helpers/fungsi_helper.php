<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('alt')) {
	function alt($key)
	{
		if ($key == "1") {
			return "pelita mutiara indah, gambar lift, gambar eskalator, perusahaan lift, lift dan eskalator, Elevator,elevator indonesia,elevator jakarta,elevator schneider,Eskalator,eskalator dan lift,harga lift,home lift,jual eskalator,jual lift,Lift,lift barang,lift dan eskalator,lift indonesia,lift jakarta,lift penumpang,lift rumah sakit,lift schneider,lift wells,schneider elevator,schneider elevatorindonesia,schneider lift,spare part lift,wells elevator,wells elevatorindonesia,agen schneider,agen schneider indonesia,agen wells,agen wells elevator jakarta,agen wells indonesia,distributor lift di jakarta,dumbwaiter,Elevator penumpang,eskalator barang,Eskalator jakarta,Hospital elevator,Importir lift,Jakarta Elevator,Jakarta eskalator,Jakarta lift,Kontraktor lift di indonesia,Lift di jakarta,Lift kafe,Lift lokal,Lift passenger,lift untuk rumah tinggal,mesin lift barang,Room elevator,Roomless elevator,car & freigh elevator,car lift,dumbwaiter indonesia,dump waiter,elevator barang,elevator dumbwaiter,escalator walking,freigh elevator,harga elevator,harga escalator,harga eskalator,harga home lift,harga lift barang,harga lift penumpang,harga lift rumah,home lift indonesia,jual dumbwaiter,jual elevator,jual lift barang,jual lift penumpang,jual lift rumah,kontraktor lift,lift barang jakarta,lift rumah tinggal,panoramic lift,walk escalator,PMI LIFT";
		} else {
			return $key . " pelita mutiara indah";
		}
	}
}

if (!function_exists('cache')) {
	function cache()
	{
		return "?" . date("dmyhis");
	}
}
if (!function_exists('akt_page')) {
	function akt_page($page, $url)
	{
		if ($page == $url) {
			return 'id="current"';
		}
	}
}
if (!function_exists('tgl_indo')) {
	function tgl_indo($tgl)
	{
		$ubah = gmdate($tgl, time() + 60 * 60 * 8);
		$pecah = explode("-", $ubah);
		$tanggal = $pecah[2];
		$bulan = bulan($pecah[1]);
		$tahun = $pecah[0];
		return $tanggal . ' ' . $bulan . ' ' . $tahun;
	}
}

if (!function_exists('br')) {
	function br($sintak)
	{
		if (is_array($sintak)) {
			foreach ($sintak as $key => $value) {
				echo "<i style='color:red !important;'>" . $value . "</i> - ";
			}
		} else {
			echo "<br/><i style='color:red !important;'>" . $sintak . "</i>";
		}
	}
}
if (!function_exists('debug')) {
	function debug($sintak, $posisi)
	{
		if ($posisi == "") {
			$pos = "left:0";
		} else {
			$pos = "right:0";
		}
		echo "<pre style='position:fixed;z-index:999999999999;background:#ffffffc9;$pos'>" . print_r($sintak, true) . "</pre>";
	}
}
if (!function_exists('dbg')) {
	function dbg($sintak)
	{
		echo "<pre style='background:#ffffffc9'>" . print_r($sintak, true) . "</pre>";
	}
}
if (!function_exists('vdump')) {
	function vdump($sintak)
	{
		return print_r($sintak, true);
	}
}
if (!function_exists('ddbg')) {
	function ddbg($sintak)
	{
		die(dbg($sintak));
	}
}
if (!function_exists('prin')) {
	function prin($sintak)
	{
		echo "<pre style='position:fixed;z-index:999999999999;top:0;background:#ffffffc9;'>" . print_r($sintak, true) . "</pre>";
	}
}
if (!function_exists('Sql')) {
    function Sql($data)
    {
        $id = 'dbg_' . uniqid();

        echo '
        <div style="
            position:fixed;
            right:10px;
            bottom:10px;
            z-index:999999999;
            width:20vw;
            max-width:20vw;
            min-width:250px;
            background:#fff;
            border:1px solid #ddd;
            border-radius:4px;
            box-shadow:0 0 10px rgba(0,0,0,.15);
        ">
            <div class="bg-danger text-light" style="
                padding:5px 8px;
                border-bottom:1px solid #eee;
                display:flex;
                justify-content:space-between;
                align-items:center;
            ">
                <small><b>DEBUG</b></small>

                <button
                    class="btn btn-xs btn-light"
                    onclick="navigator.clipboard.writeText(document.getElementById(\''.$id.'\').innerText)"
                    title="Copy">
                    <i class="fa fa-copy"></i>
                </button>
            </div>

            <pre id="'.$id.'" style="
                margin:0;
                padding:8px;
                max-height:30vh;
                overflow:auto;
                font-size:11px;
                background:#fafafa;
            ">'.htmlspecialchars(print_r($data, true)).'</pre>
        </div>';
    }
}
if (!function_exists('popup')) {
	function popup($no, $sintak)
	{
		if ($no == 0) {
			$no = "top:0";
		} else {
			$no = "top:" . ($no * 35) . "px";
		}
		echo "<pre style='background:#e5e5e5e3;position:fixed;color:#000;font-size:14px;z-index:9999999;padding:5px 20px;border:solid 1px #ccc;$no'>" . print_r($sintak, true) . "</pre>";
	}
}

if (!function_exists('trace')) {
	function trace($tipe)
	{
		$executionTime = new ExecutionTime();
		if ($tipe == "start") {
			$executionTime->start();
		} else {
			$executionTime->end();
		}
		echo $executionTime;
	}
}

class ExecutionTime
{
	private $startTime;
	private $endTime;

	public function start()
	{
		$this->startTime = getrusage();
	}

	public function end()
	{
		$this->endTime = getrusage();
	}

	private function runTime($ru, $rus, $index)
	{
		return ($ru["ru_$index.tv_sec"] * 1000 + intval($ru["ru_$index.tv_usec"] / 1000))
			-  ($rus["ru_$index.tv_sec"] * 1000 + intval($rus["ru_$index.tv_usec"] / 1000));
	}

	public function __toString()
	{
		return "This process used " . $this->runTime($this->endTime, $this->startTime, "utime") .
			" ms for its computations\nIt spent " . $this->runTime($this->endTime, $this->startTime, "stime") .
			" ms in system calls\n";
	}
}

if (!function_exists('encrypt_id')) {
	function encrypt_id($id)
	{
		if (empty($id)) {
			$id = md5(date("YmdH"));
		}
		$keyword = "adelia";
		$panjang_key  = strlen($keyword);
		$ins_key = $panjang_key;
		$hasil = "";
		for ($key = 0; $key <= $panjang_key; $key++) {
			$rumus = $key * $ins_key;
			$hasil .= substr($keyword, $key, 1) . substr($id, $rumus, $ins_key);
		}
		//3252352-sdgds-sdgd-dsgs-sgs332fs3f
		#7,5,4,4;
		$hasil = substr($hasil, 0, 10) . "-" . substr($hasil, 10, 8) . "-" . substr($hasil, 18, 6) . "-" . substr($hasil, 24, 5) . "-" . substr($hasil, 29, 30);
		return trim($hasil);
	}
}

if (!function_exists('decrypt_id')) {
	function decrypt_id($id)
	{
		$id    = str_replace("-", "", $id);
		$keyword = "adelia";
		$panjang_key  = strlen($keyword);
		$ins_key = $panjang_key;
		$hasil = "";
		for ($key = 0; $key <= $panjang_key; $key++) {
			$rumus = ($key * $ins_key);
			$char  = substr($keyword, $key, 1);
			$new = substr($id, $rumus, $ins_key);
			if (substr($id, $rumus, 1) == $char) {
				$hasil .= substr($id, $key + 1, strlen($new));
			} else {
				$hasil .= substr($id, $rumus + $key + 1, strlen($new));
			}
		}
		return $hasil;
	}
}
if (!function_exists('encrypt_pass')) {
	function encrypt_pass($password)
	{
		$keyword = "abizar";
		$panjang_key  = strlen($keyword);
		$panjang_pass = strlen($password);
		$ins_key = $panjang_key;
		$hasil = "";
		for ($key = 0; $key <= $panjang_key; $key++) {
			$rumus = $key * $ins_key;
			$hasil .= substr($keyword, $key, 1) . substr($password, $rumus, $ins_key);
		}
		return $hasil;
	}
}

if (!function_exists('get_header')) {
	function get_header($headerKey)
	{
		$test = getallheaders();
		if (array_key_exists($headerKey, $test)) {
			$headerValue = $test[$headerKey];
		} else {
			$headerValue = "";
		}
		return $headerValue;
	}
}
if (!function_exists('bulan')) {
	function bulan($bln)
	{
		switch ($bln) {
			case 1:
				return "Januari";
				break;
			case 2:
				return "Februari";
				break;
			case 3:
				return "Maret";
				break;
			case 4:
				return "April";
				break;
			case 5:
				return "Mei";
				break;
			case 6:
				return "Juni";
				break;
			case 7:
				return "Juli";
				break;
			case 8:
				return "Agustus";
				break;
			case 9:
				return "September";
				break;
			case 10:
				return "Oktober";
				break;
			case 11:
				return "November";
				break;
			case 12:
				return "Desember";
				break;
		}
	}
}

if (!function_exists('nama_hari')) {
	function nama_hari($tanggal)
	{
		$ubah = gmdate($tanggal, time() + 60 * 60 * 8);
		$pecah = explode("-", $ubah);
		$tgl = $pecah[2];
		$bln = $pecah[1];
		$thn = $pecah[0];

		$nama = date("l", mktime(0, 0, 0, $bln, $tgl, $thn));
		$nama_hari = "";
		if ($nama == "Sunday") {
			$nama_hari = "Minggu";
		} else if ($nama == "Monday") {
			$nama_hari = "Senin";
		} else if ($nama == "Tuesday") {
			$nama_hari = "Selasa";
		} else if ($nama == "Wednesday") {
			$nama_hari = "Rabu";
		} else if ($nama == "Thursday") {
			$nama_hari = "Kamis";
		} else if ($nama == "Friday") {
			$nama_hari = "Jumat";
		} else if ($nama == "Saturday") {
			$nama_hari = "Sabtu";
		}
		return $nama_hari;
	}
}

if (!function_exists('hitung_mundur')) {
	function hitung_mundur($wkt)
	{
		$waktu = array(
			365 * 24 * 60 * 60	=> "tahun",
			30 * 24 * 60 * 60		=> "bulan",
			7 * 24 * 60 * 60		=> "minggu",
			24 * 60 * 60		=> "hari",
			60 * 60			=> "jam",
			60				=> "menit",
			1				=> "detik"
		);

		$hitung = strtotime(gmdate("Y-m-d H:i:s", time() + 60 * 60 * 8)) - $wkt;
		$hasil = array();
		if ($hitung < 5) {
			$hasil = 'kurang dari 5 detik yang lalu';
		} else {
			$stop = 0;
			foreach ($waktu as $periode => $satuan) {
				if ($stop >= 6 || ($stop > 0 && $periode < 60)) break;
				$bagi = floor($hitung / $periode);
				if ($bagi > 0) {
					$hasil[] = $bagi . ' ' . $satuan;
					$hitung -= $bagi * $periode;
					$stop++;
				} else if ($stop > 0) $stop++;
			}
			$hasil = implode(' ', $hasil) . ' yang lalu';
		}
		return $hasil;
	}
}

#openview

define('Mpath', 'pageturner/');

if (!function_exists('min_space')) {
	function min_space($content, $data)
	{
		if ($data || $data == true || $data == "-") {
			return str_replace(" ", "-", $content);
		} else {
			return str_replace("-", " ", $content);
		}
	}
}

if (!function_exists('ov_img')) {
	function ov_img($page, $path, $tipe)
	{
		if ($tipe == "small" || $tipe == "") {
			$xpath = "thumb/";
		} else {
			$xpath = "page/";
		}
		$ov_img = Mpath . $path . "/files/" . $xpath . $page . ".jpg";
		if (file_exists($ov_img)) {
			$ov_img = $ov_img;
		} else {
			$ov_img = "assets/images/noimage.png";
		}
		return $ov_img;
	}
}
if (!function_exists('ov_img_stat')) {
	function ov_img_stat($page, $path, $tipe)
	{
		if ($tipe == "small" || $tipe == "") {
			$xpath = "thumb/";
		} else {
			$xpath = "page/";
		}
		$ov_img = "files/" . $xpath . $page . ".jpg";
		if (file_exists($ov_img)) {
			$ov_img = $ov_img;
		} else {
			$ov_img = "assets/images/noimage.png";
		}
		return $ov_img;
	}
}

if (!function_exists('api_ov_img')) {
	function api_ov_img($page, $path, $tipe)
	{
		if ($tipe == "small" || $tipe == "") {
			$xpath = "thumb/";
		} elseif ($tipe == "med" || $tipe == "") {
			$xpath = "medium/";
		} else {
			$xpath = "page/";
		}
		$ov_img = "files/" . $xpath . $page . ".jpg";
		/*if(file_exists($ov_img)){
			$ov_img = $ov_img;
		}else{
			$ov_img = "files/extfile/noimage.png";
		}*/
		return $ov_img;
	}
}

if (!function_exists('array_sort')) {
	function array_sort($array, $on, $order = SORT_ASC)
	{
		$new_array = array();
		$sortable_array = array();
		if ($array) {
			if (count($array) > 0) {
				foreach ($array as $k => $v) {
					if (is_array($v)) {
						foreach ($v as $k2 => $v2) {
							if ($k2 == $on) {
								$sortable_array[$k] = $v2;
							}
						}
					} else {
						$sortable_array[$k] = $v;
					}
				}
				switch ($order) {
					case SORT_ASC:
						asort($sortable_array);
						break;
					case SORT_DESC:
						arsort($sortable_array);
						break;
				}
				foreach ($sortable_array as $k => $v) {
					$new_array[$k] = $array[$k];
				}
			}
			return $new_array;
		}
	}
}
if (!function_exists('db_array')) {
	function db_array($array, $tipe)
	{
		#debug($array);
		if ($tipe == "select") {
			$result = "";
			$x = 0;
			foreach ($array as $key => $value) {
				if ($x == 0) {
					$where = " where ";
				} else {
					$where = " and ";
				}
				$result .= $where . $key . "='" . $value . "'";
				$x++;
			}
		} elseif ($tipe == "update") {
			$result = "set ";
			$x = 1;
			$z = count($array);
			foreach ($array as $key => $value) {
				if ($x == $z) {
					$where = " where " . array_key_first($array) . "='" . array_values($array)[0] . "'";
				} else {
					$where = ",";
				}
				if ($x != 1) {
					$result .= $key . "='" . $value . "'" . $where;
				}
				$x++;
			}
		} elseif ($tipe == "insert") {
			$result = "";
			$kolom  = "";
			$data   = "";
			$x = 1;
			$z = count($array);
			foreach ($array as $key => $value) {
				if ($x == 1) {
					$where = "(";
					$were  = "";
				} elseif ($x == $z) {
					$where = ",";
					$were  = ")";
				} else {
					$where = ",";
					$were  = "";
				}
				$kolom .= $where . $key . $were;
				$data  .= $where . "'" . $value . "'" . $were;
				$x++;
			}
			$result = $kolom . " values " . $data;
		}
		return $result;
	}
}
/*if ( ! function_exists('ov_img')){
	function ov_img($page,$path){
		if($page=="1"){

		}else{

		}
	}
}
*/
#custom function 

if (!function_exists('ganjil_genap')) {
	function ganjil_genap($number)
	{
		if (is_numeric($number)) {
			if ($number % 2 == 0) {
				return array($number, $number + 1);
			} else {
				return array($number - 1, $number);
			}
		} else {
			return array(1);
		}
	}
}
if (!function_exists('gg')) {
	function gg($number)
	{
		if (is_numeric($number)) {
			if ($number % 2 == 0) {
				return "genap";
			} else {
				return "ganjil";
			}
		} else {
			return "0";
		}
	}
}

// if (!function_exists('siak_url')) {
// 	function siak_url($url){
// 		$this->config->item("siak_url");
// 	}
// }

if (!function_exists('get_img_user')) {
	function get_img_user($img_user)
	{
		$path = "assets/images/";
		$img  = $path."cp-head-square.png";		
		// echo FCPATH;
		if (!empty($img_user)) {
			$img_user = $path.$img_user;
			if (file_exists($img_user)) {
				$img = base_url($img_user);
			} else {
				$img = base_url($img);
			}
		} else {
			$img = base_url($img);
		}
		return $img;
	}
}
if (!function_exists('get_img')) {
	function get_img($img_path)
	{
		if (file_exists($img_path)) {
			$img = $img_path;
		} else {
			$img = "assets/images/noimage.png";
		}
		return base_url($img);
	}
}
if (!function_exists('magz_img')) {
	function magz_img($img_path)
	{
		$img = "assets/images/nomagz.png";
		if (empty($img_path) || strtolower($img_path) == "x") {
			$img = $img;
		} else {
			if (file_exists($img_path)) {
				if (is_dir($img_path)) {
					$img = $img;
				} else {
					$img = $img_path;
				}
			} else {
				$img = $img;
			}
			$img_path;
		}
		return base_url($img);
	}
}
if (!function_exists('get_cover')) {
	function get_cover($img_path)
	{
		if (file_exists($img_path)) {
			$img = base_url($img_path);
		} else {
			$img = "x";
		}
		return $img;
	}
}
if (!function_exists('all_query')) {
	function all_query($sql)
	{
		if ($sql->num_rows() > 1) {
			$result =  result_query($sql);
		} else {
			if ($sql->num_rows() != 0) {
				$result =  array(single_query($sql));
			} else {
				$result = "";
			}
		}
		return $result;
	}
}
if (!function_exists('each_query')) {
	function each_query($sintak)
	{
		$query  = $sintak;
		if ($query) {
			if (count((array)$query->result()) == 0) {
				// direct("404");
				// die();
				$result = "";
			} else {
				foreach ($query->result() as $row) {
					$result[] = $row;
				}
				return $result;
			}
		}
	}
}
if (!function_exists('single_query')) {
	function single_query($sintak)
	{
		$query  = $sintak;
		if ($query) {
			if (count((array)$query->result()) == 0) {
				// direct("404");
				// die();
				$result = "";
			} else {
				$result = $query->result()[0];
			}
		} else {
			$result = "not found";
		}
		return $result;
	}
}
if (!function_exists('result_query')) {
	function result_query($sintak)
	{
		$query  = $sintak;
		#echo h3($query->num_rows()."xxx".count((array)$query->result()));
		if ($query) {
			#echo h3(count((array)$query->result()));
			if ($query->num_rows() == 0) {
				$result = "";
			} elseif ($query->num_rows() == 1) {
				$result = $query->result();
			} else {
				foreach ($query->result() as $row) {
					$result[] = $row;
				}
				return $result;
			}
		} else {
			$result = "not found";
		}
		return $result;
	}
}

if (!function_exists('error_log')) {
	function error_log($sintak)
	{
		return $sintak;
	}
}


if (!function_exists('Mname')) {
	function Mname($magazine_name)
	{
		if (strpos(strtolower($magazine_name), "magazine") === false) {
			$M_name = $magazine_name . " Magazine";
		} else {
			$M_name = $magazine_name;
		}
		return $M_name;
	}
}


if (!function_exists('istatus')) {
	function istatus($cnv, $pub)
	{
		if ($cnv == "0") {
			$stat = "Not Convert";
		} elseif ($cnv == "1") {
			$stat = "Process Convert";
		} elseif ($cnv == "2") {
			if ($pub == "0") {
				$stat = "Ready Publish";
			} else {
				$stat = "Pageturner Ready";
			}
		}
		return "<i>$stat</i>";
	}
}

function paragrap($sentence, $word_count)
{
	$space_count = 0;
	$print_string = '';
	for ($i = 0; $i < strlen($sentence); $i++) {
		if ($sentence[$i] == ' ')
			$space_count++;
		$print_string .= $sentence[$i];
		if ($space_count == $word_count)
			break;
	}
	if ($word_count >= 13) {
		return $print_string;
	} else {
		return $print_string;
	}
}

if (!function_exists('Host')) {
	function Host()
	{
		return $_SERVER["HTTP_HOST"];
	}
}
if (!function_exists('NN')) {
	function NN($value)
	{

		return $value==0?'':$value;
	}
}

if (!function_exists('admin_url')) {
	function admin_url($url)
	{
		if($_SERVER['SERVER_PORT']=="808"){
			$base = "http://localhost/ci3/nk/".$url;
		}else{
			if($_SERVER['HTTP_HOST']=="siak.nurulkarimah.sch.id"){
				$base = "https://nurulkarimah.sch.id/".$url;
			}else{
				$base = base_url($url);
			}
		}
		return $base;
	}
}
if (!function_exists('admin_page')) {
	function admin_page($url)
	{
		return base_url('admin/' . $url);
	}
}
if (!function_exists('flip_url')) {
	function flip_url($url)
	{
		if (!Host() == "localhost") {
			return 'https://flip.versoview.com/' . $url;
		} else {
			return base_url(Mpath . $url);
		}
	}
}
if (!function_exists('assets')) {
	function assets($url)
	{
		return base_url('assets/' . $url);
	}
}
if (!function_exists('magz')) {
	function magz($url)
	{
		return base_url('magazine/' . $url);
	}
}

if (!function_exists('left')) {
	function left($str, $length)
	{
		return substr($str, 0, $length);
	}
}
if (!function_exists('right')) {
	function right($str, $length)
	{
		return substr($str, -$length);
	}
}
if (!function_exists('direct')) {
	function direct($url)
	{
		echo "<meta http-equiv='refresh' content='0;URL=" . base_url("404") . "' />";
	}
}

if (!function_exists('h3')) {
	function h3($text)
	{
		echo "<div><h3  style='margin-top:100px;text-align:center;background:black;color:#fff;padding:10px 0;'>" . $text . "</h3></div>";
	}
}

if (!function_exists('issue')) {
	function issue($issue)
	{
		# echo "<div><h3  style='margin-top:100px;text-align:center;background:black;color:#fff;padding:10px 0;'>".$text."</h3></div>";
	}
}
if (!function_exists('Uw')) {
	function Uw($value)
	{
		return ucwords(strtolower($value));
	}
}

if (!function_exists('tgl')) {
	function tgl($tipe)
	{
		if ($tipe == "sort") {
			$result = date("ymdHi");
		} elseif ($tipe == "sort2") {
			$result = date("ymdHis");
		} elseif ($tipe == "full") {
			$result = date("Y-m-d H:i:s");
		} elseif ($tipe == "tgl") {
			$result = date("Y-m-d");
		} elseif ($tipe == "waktu") {
			$result = date("H:i:s");
		} else {
			$result = date($tipe);
		}
		return $result;
		# echo "<div><h3  style='margin-top:100px;text-align:center;background:black;color:#fff;padding:10px 0;'>".$text."</h3></div>";
	}
}

if (!function_exists('ak_img_resize')) {
	function ak_img_resize($target, $newcopy, $w, $h, $ext)
	{
		list($w_orig, $h_orig) = getimagesize($target);
		$scale_ratio = $w_orig / $h_orig;
		if (($w / $h) > $scale_ratio) {
			$w = $h * $scale_ratio;
		} else {
			$h = $w / $scale_ratio;
		}
		$img = "";
		$ext = strtolower($ext);
		if ($ext == "gif") {
			$img = imagecreatefromgif($target);
		} else if ($ext == "png") {
			$img = imagecreatefrompng($target);
		} else {
			$img = imagecreatefromjpeg($target);
		}
		$tci = imagecreatetruecolor($w, $h);
		// imagecopyresampled(dst_img, src_img, dst_x, dst_y, src_x, src_y, dst_w, dst_h, src_w, src_h)

		imagefilter($img, IMG_FILTER_PIXELATE, 1);
		imagefilter($img, IMG_FILTER_PIXELATE, 1, true);
		//var_dump($tci.$img);
		imagecopyresampled($tci, $img, 0, 0, 0, 0, $w, $h, $w_orig, $h_orig);
		if ($ext == "gif") {
			imagegif($tci, $newcopy);
		} else if ($ext == "png") {
			imagepng($tci, $newcopy);
		} else {
			imagejpeg($tci, $newcopy, 80);
		}
	}
}

if (!function_exists('ak_img_resize_test')) {
	function ak_img_resize_test($target, $newcopy, $w, $h, $ext)
	{
		list($w_orig, $h_orig) = getimagesize($target);
		$scale_ratio = $w_orig / $h_orig;
		if (($w / $h) > $scale_ratio) {
			$w = $h * $scale_ratio;
		} else {
			$h = $w / $scale_ratio;
		}
		$img = "";
		$ext = strtolower($ext);
		if ($ext == "gif") {
			$img = imagecreatefromgif($target);
		} else if ($ext == "png") {
			$img = imagecreatefrompng($target);
		} else {
			$img = imagecreatefromjpeg($target);
		}
		$tci = imagecreatetruecolor($w, $h);
		// imagecopyresampled(dst_img, src_img, dst_x, dst_y, src_x, src_y, dst_w, dst_h, src_w, src_h)

		imagefilter($img, IMG_FILTER_PIXELATE, 1);
		imagefilter($img, IMG_FILTER_PIXELATE, 1, true);
		//var_dump($tci.$img);

		imagecopyresampled($tci, $img, 0, 0, 0, 0, $w, $h, $w_orig, $h_orig);
		if ($ext == "gif") {
			imagegif($tci, $newcopy);
		} else if ($ext == "png") {
			imagepng($tci, $newcopy);
		} else {
			imagejpeg($tci, $newcopy, 80);
		}
	}
}



if (!function_exists('foldersize')) {
	function foldersize($path)
	{
		$total_size = 0;
		$files = scandir($path);

		foreach ($files as $t) {
			if (is_dir(rtrim($path, '/') . '/' . $t)) {
				if ($t <> "." && $t <> "..") {
					$size = foldersize(rtrim($path, '/') . '/' . $t);

					$total_size += $size;
				}
			} else {
				$size = filesize(rtrim($path, '/') . '/' . $t);
				$total_size += $size;
			}
		}
		return $total_size;
	}
}

if (!function_exists('scandirek')) {
    function scandirek($path)
    {
        $scan = scandir($path, 1);
        foreach ($scan as $keys => $folder) {
		echo $folder;
        }
    }
}
if (!function_exists('sys')) {
	function sys($tipe)
	{
		if (DIRECTORY_SEPARATOR == '\\') {
			if (file_exists("D:\\xampp\\htdocs\\adeliafirdaus.txt")) {
				$dir   = "D:\\xampp\\htdocs\\agencyfish\\weblist\\versoview\\";
				$conv  = "magick ";
				$dele  = "del /q ";
				$copy  = "copy ";
				$move  = "move ";
				$rmdir = "rd  /s /q ";
				$mkdir = "md ";
			} else {
				$dir   = "D:\\xampp\\htdocs\\agencyfish\\weblist\\versoview\\";
				$conv  = "magick ";
				$dele  = "del /q ";
				$copy  = "copy ";
				$move  = "move ";
				$rmdir = "rd  /s /q ";
				$mkdir = "md ";
				echo die(h3("silahkan setting fungsi_helper nya"));
			}
		} else {
			$dir   = "/var/www/html/versoview/";
			$conv  = "convert ";
			$dele  = "sudo rm ";
			$copy  = "cp ";
			$move  = "mv ";
			$rmdir = "sudo rm -rf ";
			$mkdir = "mkdir -p ";
		}
		if (strtolower($tipe) == "path") {
			return $dir;
		} elseif (strtolower($tipe) == "conv") {
			return $conv;
		} elseif (strtolower($tipe) == "dele") {
			return $dele;
		} elseif (strtolower($tipe) == "copy") {
			return $copy;
		} elseif (strtolower($tipe) == "rmdir") {
			return $rmdir;
		} elseif (strtolower($tipe) == "mkdir") {
			return $mkdir;
		} elseif (strtolower($tipe) == "move") {
			return $move;
		} else {
			return false;
		}
	}
}

if (!function_exists('numPdf')) {
	function numPdf($pdffile)
	{
		if (DIRECTORY_SEPARATOR == '\\') {
			$numpage   = shell_exec("identify -format %n, " . $pdffile);
			$num       = explode(",", $numpage);
			if (is_array($num)) {
				return $page      = $num[0];
			} else {
				return shell_exec("identify -format %n " . $pdffile);
			}
		} else {
			return shell_exec("pdfinfo " . $pdffile . "  | grep Pages: | awk '{print $2}'");
		}
	}
}

if (!function_exists('resPdf')) {
	function resPdf($pdffile)
	{
		if (DIRECTORY_SEPARATOR == '\\') {
			$numpage   = shell_exec("identify -format %n, " . $pdffile);
			$num       = explode(",", $numpage);
			if (is_array($num)) {
				return $page      = $num[0];
			} else {
				return shell_exec("identify -format %n " . $pdffile);
			}
		} else {
			return shell_exec("pdfinfo " . $pdffile . "  | grep 'Page Size': | awk '{print $3,$5}'");
		}
	}
}
if (!function_exists('izrand')) {
	function izrand($length = 32, $numeric = false)
	{
		$random_string = "";
		while (strlen($random_string) < $length && $length > 0) {
			if ($numeric === false) {
				$randnum = mt_rand(0, 61);
				$random_string .= ($randnum < 10) ?
					chr($randnum + 48) : ($randnum < 36 ?
						chr($randnum + 55) : $randnum + 61);
			} else {
				$randnum = mt_rand(0, 9);
				$random_string .= chr($randnum + 48);
			}
		}
		return $random_string;
	}
}
if (!function_exists('generateRandomString')) {
	function generateRandomString($length = 10)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZAbizarAdeli4';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
}
if (!function_exists('ranKey')) {
	function ranKey($value)
	{
		$addstr = "AbizarAdeli4";
		if (empty($value)) {
			$value 	= 10;
			$addstr = "";
		}
		#echo " ** ".$value;
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890AbizarAdeli4' . $addstr;
		$pass = array(); //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		$rand 	= rand(0, 9);
		for ($i = 0; $i < $value; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass) . $rand; //turn the array into a string
	}
}

if (!function_exists('encrypt_id')) {
	function encrypt_id($id)
	{
		if (empty($id)) {
			$id = md5(date("YmdH"));
		}
		$keyword = "adelia";
		$panjang_key  = strlen($keyword);
		$ins_key = $panjang_key;
		$hasil = "";
		for ($key = 0; $key <= $panjang_key; $key++) {
			$rumus = $key * $ins_key;
			$hasil .= substr($keyword, $key, 1) . substr($id, $rumus, $ins_key);
		}
		//3252352-sdgds-sdgd-dsgs-sgs332fs3f
		#7,5,4,4;
		$hasil = substr($hasil, 0, 10) . "-" . substr($hasil, 10, 8) . "-" . substr($hasil, 18, 6) . "-" . substr($hasil, 24, 5) . "-" . substr($hasil, 29, 30);
		return trim($hasil);
	}
}
if (!function_exists('decrypt_id')) {
	function decrypt_id($id)
	{
		$id    = str_replace("-", "", $id);
		$keyword = "adelia";
		$panjang_key  = strlen($keyword);
		$ins_key = $panjang_key;
		$hasil = "";
		for ($key = 0; $key <= $panjang_key; $key++) {
			$rumus = ($key * $ins_key);
			$char  = substr($keyword, $key, 1);
			$new = substr($id, $rumus, $ins_key);
			if (substr($id, $rumus, 1) == $char) {
				$hasil .= substr($id, $key + 1, strlen($new));
			} else {
				$hasil .= substr($id, $rumus + $key + 1, strlen($new));
			}
		}
		return $hasil;
	}
}
if (!function_exists('encrypt_pass')) {
	function encrypt_pass($password)
	{
		$keyword = "abizar";
		$panjang_key  = strlen($keyword);
		$panjang_pass = strlen($password);
		$ins_key = $panjang_key;
		$hasil = "";
		for ($key = 0; $key <= $panjang_key; $key++) {
			$rumus = $key * $ins_key;
			$hasil .= substr($keyword, $key, 1) . substr($password, $rumus, $ins_key);
		}
		return $hasil;
	}
}
if (!function_exists('SeS')) {
	function Ses($key)
	{
		if (empty($key)) {
			$key = encrypt_id(md5(tgl("sort") . ranKey("")));
		} else {
			$key = decrypt_id($key);
		}
		return $key;
	}
}
#custom function 
if (!function_exists('url_exists')) {
	function url_exists($url)
	{
		if (!$fp = curl_init($url)) return false;
		return true;
	}
}

if (!function_exists('img_blank')) {
	function img_blank($tipe)
	{
		if ($tipe == "full" || $tipe == "1") {
			$img = "assets/images/pelita_schneider_blank_clear.png";
		} else {
			$img = "assets/images/logo_pelita_blank_clear.png";
		}
		return str_replace("//", "/", $img);
	}
}
if (!function_exists('img_gallery')) {
	function img_gallery($img_path, $img_name)
	{
		$img = "assets/images/logo_pelita_blank2.png";
		if (!empty($img_name)) {
			$asli = $img_path . "/sm_" . $img_name;
			if (file_exists($asli)) {
				$img = $img_path . "/sm_" . $img_name;
			} else {
				$img = $img_path . "/" . $img_name;
			}
		}
		return str_replace("//", "/", $img);
	}
}
if (!function_exists('media_file')) {
	function media_file($media_file)
	{
		$img = "assets/images/image-not-found.jpg";
		if (!empty($media_file)) {
			if (file_exists($media_file)) {
				$img = $media_file;
			}
		}
		return str_replace("//", "/", $img);
	}
}

if (!function_exists('img_gallery_tipe')) {
	function img_gallery_tipe($img_tipe, $img_path, $img_name)
	{
		$img = "assets/images/logo_pelita_blank2.png";
		if (!empty($img_name)) {
			$asli = "media/" . $img_name;
			if ($img_tipe == "small") {
				$img = "media/" . $img_path . "small_" . $img_name;
			} else {
				$img = $asli;
			}
		}
		return $img;
	}
}
if (!function_exists('get_img')) {
	function get_img($img_path)
	{
		if (file_exists($img_path)) {
			$img = $img_path;
		} else {
			$img = "assets/images/pmi-blank.png";
		}
		return base_url($img);
	}
}

if (!function_exists('active')) {
	function active($link, $uri, $tipe)
	{
		if ($link == "home" && $uri == "") {
			return " active";
		} else {
			if(is_array($link)){
				foreach ($link as $key => $value) {
					if($value==$uri){
						if ($tipe == "0") {
							return " active";
						} elseif ($tipe == "1") {
							return "class='active1'";
						} else {
							return "class='active2'";
						}
					}
				}
			}else{
				if ($link == $uri) {
					if ($tipe == "0") {
						return " active";
					} elseif ($tipe == "1") {
						return "class='active1'";
					} else {
						return "class='active2'";
					}
				}
			}
		}
	}
}
if (!function_exists('pilih')) {
	function pilih($link, $uri, $cls1,$cls2)
	{
		if($link=="" || $link=="all" && $uri==""){
			$class = $cls1;
		}else{
			if($link==$uri){				
				$class = $cls1;
			}else{
				$class = $cls2;
			}
		}
		return $class;
	}
}

if (!function_exists('active_menu')) {
	function active_menu($link, $uri)
	{
		if ($link == $uri) {
			return 'style = "display: block"';
		}
	}
}
if (!function_exists('aktif')) {
	function aktif($link)
	{
		$url = uri_string();
		$url = explode("/", $url);
		if (is_array($url)) {
			if ($link == $url[0]) {
				return " id='current'";
			} else {
				return " ";
			}
		} else {
			if ($link == $url) {
				return " id='current'";
			} else {
				return " ";
			}
		}
	}
}
if (!function_exists('nolnul')) {
	function nolnul($value)
	{
		if ($value == "0") {
			$val = "";
		} else {
			$val = $value;
		}
		return $val;
	}
}

if (!function_exists('imgResize')) {
	function imgResize($xtarget, $newcopy, $w, $h, $ext, $path)
	{
		$target  = $path . $xtarget;
		$newcopy = $path . $newcopy;
		list($w_orig, $h_orig) = getimagesize($target);
		$scale_ratio = $w_orig / $h_orig;
		if (($w / $h) > $scale_ratio) {
			$w = $h * $scale_ratio;
		} else {
			$h = $w / $scale_ratio;
		}
		$img = "";
		#str_replace(search, replace, subject)
		$ext = str_replace(".", "", strtolower($ext));
		if ($ext == "gif") {
			$img = imagecreatefromgif($target);
		} else if ($ext == "png") {
			$img = imagecreatefrompng($target);
		} else {
			$img = imagecreatefromjpeg($target);
		}
		$tci = imagecreatetruecolor($w, $h);
		// imagecopyresampled(dst_img, src_img, dst_x, dst_y, src_x, src_y, dst_w, dst_h, src_w, src_h)
		imagecopyresampled($tci, $img, 0, 0, 0, 0, $w, $h, $w_orig, $h_orig);
		if ($ext == "gif") {
			$has = imagegif($tci, $newcopy);
		} else if ($ext == "png") {
			$has = imagepng($tci, $newcopy);
		} else {
			$has = imagejpeg($tci, $newcopy, 84);
		}
		#imgThumb($xtarget, $path, $w, $h, $ext);
		# echo $has;
	}
}

if (!function_exists('rTags')) {
	function rTags($input)
	{
		$arr = array("'", '"', "~", ";", "$", ">", "<", "?");
		return str_replace($arr, " ", $input);
	}
}

function base_online($link)
{
	$http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://';
	$newurl = str_replace("index.php", "", $_SERVER['SCRIPT_NAME']);
	$svr	= $http . $_SERVER['SERVER_NAME'] .  $newurl;
	if (strpos($svr, ".com") !== false) {
		#online		
		$server = str_replace("http://", "", $link);
		$server = str_replace("https://", "", $server);
		$server = str_replace("localhost/ci/pmi/", "", $server);
		$server = str_replace("elevatorindonesia.com/", "", $server);
		$server = $http . $_SERVER['SERVER_NAME'] .  $newurl . $server;
	} else {
		$server = $link;
	}
	return $server;
}

if (!function_exists('exec_prc')) {
	function exec_prc($prc,$tipe)
	{
		$CI = &get_instance();
		if ($query = $CI->db->query($prc)) {
			if (mysqli_next_result($CI->db->conn_id)) {
				return $tipe=="array" || $tipe==""? $query->result():$query->result()[0];
			}
		} else {
			return false;
		}
	}
}

if (!function_exists('expl')) {
	function expl($delimiter, $string, $default = [])
	{
		if ($string === null) {
			return $default;
		}
		if (!is_string($string)) {
			return $default;
		}
		$string = trim($string);
		if ($string === '') {
			return $default;
		}
		return explode($delimiter, $string);
	}
}

if (!function_exists("tracelog")) {
    function tracelog($logData = "",$type = "")
    {
        $CI =& get_instance();
        
        // Delete old logs (older than 3 months)
        $CI->db->where("tgl <", date("Y-m-d H:i:s", strtotime("-3 months")))
               ->delete("tracelog");
        
        $tipe = $type;
        if ($logData == "") {
            $logData = $type;
            $tipe = "query";
        }
        $router =& load_class('Router', 'core');
        $cn = $router->fetch_class();
        $mn = $router->fetch_method();
        
        $CI->load->helper('url');
        $current_url = current_url();
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $full_url = $protocol . '://' . $host . $uri;

        $route_string = $CI->uri->uri_string();
        if (empty($route_string)) {
            $route_string = '/';
        }
        $appname_with_url = $cn . '::' . $mn . '|' . $route_string;
        
        
        $data = [
            "tgl" => date("Y-m-d H:i:s"),
            "tipe" => $tipe,
            "appname" => $appname_with_url,  // Controller::method|route
            "log" => $logData . ";",
            "addid" => $mn,
        ];
        
        return $CI->db->insert("tracelog", $data);
    }
}
if (!function_exists('lQ')) {
    function lQ($log = false)
    {
        $CI =& get_instance();

        $query = '';

        // Ambil last query jika ada
        if (!empty($CI->db->last_query())) {
            $query = $CI->db->last_query();
        }

        if ($log) {
            if (method_exists($CI, 'tracelog')) {
                $CI->tracelog($query);
            } else {
                log_message('debug', $query);
            }
        }

        return $query;
    }
}

if (!function_exists("TL")) {
    function TL($data = null, $exit = false)
    {
        $CI =& get_instance();
        
        // Delete old logs
        $CI->db->where("tgl <", date("Y-m-d H:i:s", strtotime("-3 months")))
               ->delete("tracelog");
        
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $caller = $backtrace[0] ?? ['file' => 'unknown', 'line' => 0];
        
        // Determine what to log
        $logData = "";
        $tipe = "manual";
        
        if ($data === null) {
            $logData = "FILE: " . $caller['file'] . " LINE: " . $caller['line'];
            $tipe = "trace";
        } elseif ($data === "lQ" || $data === "last_query") {
            $logData = $CI->db->last_query();
            $tipe = "query";
            
            // Tampilkan juga di browser
            if (ENVIRONMENT === 'development') {
                echo '<pre style="background:#f8f9fa; border:1px solid #28a745; padding:10px; margin:10px 0; border-radius:5px; color:#28a745; font-family:monospace;">';
                echo '<strong style="color:#28a745;">🔍 QUERY:</strong><br>';
                echo htmlspecialchars($logData);
                echo '</pre>';
            }
        } elseif (is_array($data) || is_object($data)) {
            $logData = print_r($data, true);
            
            if (ENVIRONMENT === 'development') {
                echo '<pre style="background:#f8f9fa; border:1px solid #dc3545; padding:10px; margin:10px 0; border-radius:5px;">';
                echo '<strong style="color:#dc3545;">📦 DATA:</strong><br>';
                echo htmlspecialchars($logData);
                echo '</pre>';
            }
        } else {
            $logData = $data;
            
            if (ENVIRONMENT === 'development') {
                echo '<pre style="background:#f8f9fa; border:1px solid #ffc107; padding:10px; margin:10px 0; border-radius:5px;">';
                echo '<strong style="color:#ffc107;">📝 STRING:</strong><br>';
                echo htmlspecialchars($logData);
                echo '</pre>';
            }
        }
        
        // Get controller and method
        $router =& load_class('Router', 'core');
        $cn = $router->fetch_class();
        $mn = $router->fetch_method();
        
        $route_string = $CI->uri->uri_string() ?: '/';
        $appname_with_url = $cn . '::' . $mn . '|' . $route_string;
        
        // Insert to database
        $insert_data = [
            "tgl" => date("Y-m-d H:i:s"),
            "tipe" => $tipe,
            "appname" => $appname_with_url,
            "log" => $logData . ";",
            "addid" => $mn . ':' . $caller['line'],
        ];
        
        $CI->db->insert("tracelog", $insert_data);
        
        if ($exit) exit;
        
        return $logData;
    }
}
defined('RECAPTCHA_SITE_KEY')                 OR define('RECAPTCHA_SITE_KEY', '6LcfIFMkAAAAAEm6bllmASsMSucmmEJrpB6KTc3m');
defined('RECAPTCHA_SITE_SECRET')              OR define('RECAPTCHA_SITE_SECRET', '6LcfIFMkAAAAAC2PbdOWwiN2OdBWcKBMMMaFNpc_');
defined('RECAPTCHA_ACCEPTABLE_SPAM_SCORE')    OR define('RECAPTCHA_ACCEPTABLE_SPAM_SCORE', 0.5);


if(!function_exists('get_recapture_score')) {
    /**
     *
     * Returns a float from 0 - 1 indicating the likelihood that the request is a from a human
     * 0 is considered a robot
     * 1 is considered a human
     *
     * @param $secret
     * @return float
     */
    function get_recapture_score($secret){
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $request = $url .'?secret='.RECAPTCHA_SITE_SECRET.'&response='.$secret.'';
        $response = file_get_contents($request);
        $response = json_decode($response);

        // handle any errors
        if($response->success == false){
            foreach($response->{'error-codes'} as $code){
                switch ($code){
                    case 'missing-input-secret':
                        log_message('error', 'RECAPTCHA: The secret parameter is missing. ' . $request);
                        break;
                    case 'invalid-input-secret':
                        log_message('error', 'RECAPTCHA: The secret parameter is invalid or malformed. '. $request);
                        break;
                    case 'missing-input-response':
                        log_message('error', 'RECAPTCHA: The response parameter is missing. '. $request);
                        break;
                    case 'invalid-input-response':
                        log_message('error', 'RECAPTCHA: The response parameter is invalid or malformed. ' . $request);
                        break;
                    case 'bad-request':
                        log_message('error', 'RECAPTCHA: The request is invalid or malformed. ' . $request);
                        break;
                    case 'timeout-or-duplicate':
                        log_message('error', 'RECAPTCHA: The response is no longer valid: either is too old or has been used previously.');
                        break;
                    default:
                        log_message('error', 'RECAPTCHA: Unknown error');
                }
            }
            return 0; // treat it as spam?
        }

        return $response->score;
    }
}
if(!function_exists('get_client_ip')) {
    function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
}

if ( ! function_exists('currency')){
	function currency($input){
		if($input!=0){
			$output = number_format($input,0,",",".");
		}else{
			$output = "";
		}
		return $output;
	}
}
if ( ! function_exists('decimal')){
	function decimal($number){
		if(is_numeric($number)){
			$num = "";
			$ttl = 2 - strlen($number);
			for($x=0;$x<=$ttl;$x++){
				$num .= "0";
			}
			return $num.$number;
		}			
	}
}

if ( ! function_exists('set_expiring_session_message')){
	function set_expiring_session_message($key, $message, $seconds = 10){
		$CI =& get_instance();
		$CI->session->set_userdata($key, array(
			'message' => $message,
			'expire' => time() + (int) $seconds
		));
	}
}

if ( ! function_exists('get_expiring_session_message')){
	function get_expiring_session_message($key){
		$CI =& get_instance();
		$value = $CI->session->userdata($key);

		if (!$value) {
			return null;
		}

		$CI->session->unset_userdata($key);

		if (is_array($value)) {
			$expire = isset($value['expire']) ? (int) $value['expire'] : 0;
			if ($expire > 0 && $expire < time()) {
				return null;
			}

			return isset($value['message']) ? $value['message'] : null;
		}

		return $value;
	}
}

if ( ! function_exists('clear_expired_session_messages')){
	function clear_expired_session_messages($keys = array()){
		$CI =& get_instance();

		foreach ($keys as $key) {
			$value = $CI->session->userdata($key);
			if (!is_array($value)) {
				continue;
			}

			$expire = isset($value['expire']) ? (int) $value['expire'] : 0;
			if ($expire > 0 && $expire < time()) {
				$CI->session->unset_userdata($key);
			}
		}
	}
}
