<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {


	function __construct(){
		parent::__construct();
	}

	function get_data_karyawan(){
		$like = $this->input->post('like');
		if(empty($like)){
			$get = $this->m_data->semua('karyawan_2')->result();
		}else{
			$get = $this->m_data->cari_karyawan_like($this->input->post('like'))->result();
		}
		$result=array();
		foreach($get as $g){
				// echo $g->nama." ".strlen($g->nama)."<br>";
				array_push($result,array('nik' => $g->nik,"nama" => $g->nama,"size" => (strlen($g->nama) >= 27) ? '12' : '18' ));
		}
		echo json_encode($result);

	}

		public function migrasi2()
		{

		date_default_timezone_set("Asia/Jakarta");
		$arrContextOptions=array(
		    "ssl"=>array(
		        "verify_peer"=>false,
		        "verify_peer_name"=>false,
		    ),
		);

		$tanggal_cari = date("Y-m-d");
		$date = date("YmdHis");
		$api_key = md5('XXXX'.$tanggal_cari.$date.'XXXX');
		$url = "https://api.fingerspot.io/api/download/attendance_log/XXXX/".$tanggal_cari."/11/date_time/asc/json/".$api_key."/".$date;
		$json = file_get_contents($url,false, stream_context_create($arrContextOptions));
		$obj = json_decode($json);
		$result = array();
		$karyawan = $this->m_data->where('karyawan', array('divisi' => "60271008" ))->result();
		foreach ($karyawan as $key ) {
			for($i=0; $i <count($obj->data) ; $i++) {
		if($key->nik==$obj->data[$i]->{'NIK'}){
							$ns="i1";
							break;
		}else{
			$ns="i0";
		}
		}
		if($ns=="i0"){
		array_push($result, array('nik'=>  $key->nik,'nama' =>  $key->nama_karyawan, 'type' =>  "Tidak Scan"  ) );
		}

		}
			echo json_encode(array("value"=>1,"result"=>$result));


	 }

	 public function monitoring_divisi() {
			date_default_timezone_set("Asia/Jakarta");
			$arrContextOptions=array(
			    "ssl"=>array(
			        "verify_peer"=>false,
			        "verify_peer_name"=>false,
			    ),
			);

			if($this->input->post('status')=='Izin'){
			$date = date("Y-m-d");
				$absen_izin = $this->m_data->where('absen',array('status' => $this->input->post('pencarian'),'date_time >' => $date ))->result();

				$result = array();
				foreach ($absen_izin as $rec ) {
					array_push($result, array('nik'=>  $rec->nik ,'nama' =>  $rec->nama_karyawan,'date_time' => $rec->date_time, 'type' =>  $rec->status,'gambar_file' => $rec->gambar ) );
				}

			}elseif($this->input->post('status')=='not scan'){
				$tanggal_cari = date("Y-m-d");
				$date = date("YmdHis");
				$api_key = md5('XXXX'.$tanggal_cari.$date.'XXXX');
				$url = "https://api.fingerspot.io/api/download/attendance_log/XXXX/".$tanggal_cari."/11/date_time/asc/json/".$api_key."/".$date;
				$json = file_get_contents($url,false, stream_context_create($arrContextOptions));
				$obj = json_decode($json);
				$result = array();
				$karyawan = $this->m_data->where('karyawan', array('divisi' => $this->input->post('nik') ))->result();
				foreach ($karyawan as $key ) {
					for($i=0; $i <count($obj->data) ; $i++) {
				if($key->nik==$obj->data[$i]->{'NIK'}){
									$ns="i1";
									break;
				}else{
					$ns="i0";
				}
				}
				if($ns=="i0"){
				array_push($result, array('nik'=>  $key->nik,'nama' =>  $key->nama_karyawan, 'type' =>  "Tidak Scan"  ) );
				}

				}

				}else{
				$tanggal_cari = date("Y-m-d");
				$date = date("YmdHis");
				$api_key = md5('XXXX'.$tanggal_cari.$date.'XXXX');
				$url = "https://api.fingerspot.io/api/download/attendance_log/XXXX/".$tanggal_cari."/11/date_time/asc/json/".$api_key."/".$date;
				$json = file_get_contents($url,false, stream_context_create($arrContextOptions));
				$obj = json_decode($json);
				$result = array();
				$divisi_karyawan = $this->m_data->where('karyawan', array('divisi' => $this->input->post('nik') ))->result();
				foreach ($divisi_karyawan as $rec ) {
					for ($i=0; $i <count($obj->data) ; $i++) {
						if($this->input->post('status')==$obj->data[$i]->{'Type'}){
						if($rec->nik==$obj->data[$i]->{'NIK'}){
							$st = "m1";
							break;
						}else{
							$st="m2";
						}
					}
					}

if($st=="m1"){
	array_push($result, array('nik'=>  $obj->data[$i]->{'NIK'},'nama' =>  $obj->data[$i]->{'Name'}, 'date_time' => $obj->data[$i]->{'Date Time'}, 'type' =>  $obj->data[$i]->{'Type'}  ) );
}

				}
			}


			 echo json_encode(array("value"=>1,"result"=>$result));


	 }

	 public function harian_karyawan()
	 {
	date_default_timezone_set("Asia/Jakarta");
	$arrContextOptions=array(
			"ssl"=>array(
					"verify_peer"=>false,
					"verify_peer_name"=>false,
			),
	);
		$tanggal_cari = date("Y-m-d");
		$date = date("YmdHis");
		$api_key = md5('XXXX'.$tanggal_cari.$date.'XXXX');
		$url = "https://api.fingerspot.io/api/download/attendance_log/XXXX/".$tanggal_cari."/11/date_time/asc/json/".$api_key."/".$date;
		$json = file_get_contents($url,false, stream_context_create($arrContextOptions));
		$obj = json_decode($json);
		$absen_izin = $this->m_data->cariDataIzin($tanggal_cari,$this->input->post('nik'))->result();
		foreach ($absen_izin as $rec ) {
			$results = array('nik'=>  $rec->nik ,'nama' =>  $rec->nama_karyawan,'date_time' => $rec->date_time, 'type' =>  $rec->status,'gambar_file' => $rec->gambar );
		}
		if(empty($absen_izin)){
			$result = array();
		}else{
			$result = array($results);
		}

		for ($i=0; $i <count($obj->data) ; $i++) {
		if($this->input->post('nik')==$obj->data[$i]->{'NIK'}){
			array_push($result, array('nik'=>  $obj->data[$i]->{'NIK'},'nama' =>  $obj->data[$i]->{'Name'}, 'date_time' => $obj->data[$i]->{'Date Time'}, 'type' =>  $obj->data[$i]->{'Type'}  ) );
		}
		}


	 echo json_encode(array("value"=>1,"result"=>$result));
	 }

	public function absen_hari_ini()
	{
date_default_timezone_set("Asia/Jakarta");
$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);

if($this->input->post('pencarian')=='Izin'){
$date = date("Y-m-d");
	$absen_izin = $this->m_data->where('absen',array('status' => $this->input->post('pencarian'),'date_time >' => $date ))->result();

	$result = array();
	foreach ($absen_izin as $rec ) {
		array_push($result, array('nik'=>  $rec->nik ,'nama' =>  $rec->nama_karyawan,'date_time' => $rec->date_time, 'type' =>  $rec->status,'gambar_file' => $rec->gambar ) );
	}

}elseif($this->input->post('pencarian')=='not scan'){
	$tanggal_cari = date("Y-m-d");
	$date = date("YmdHis");
	$api_key = md5('XXXX'.$tanggal_cari.$date.'XXXX');
	$url = "https://api.fingerspot.io/api/download/attendance_log/XXXX/".$tanggal_cari."/11/date_time/asc/json/".$api_key."/".$date;
	$json = file_get_contents($url,false, stream_context_create($arrContextOptions));
	$obj = json_decode($json);
	$result = array();
	$karyawan = $this->m_data->semua('karyawan')->result();
	foreach ($karyawan as $key ) {


		for($i=0; $i <count($obj->data) ; $i++) {

	if($key->nik==$obj->data[$i]->{'NIK'}){
						$ns="i1";
						break;
	}else{
		$ns="i0";
	}

	}
	if($ns=="i0"){
	array_push($result, array('nik'=>  $key->nik,'nama' =>  $key->nama_karyawan, 'type' =>  "Tidak Scan"  ) );
	}

	}

	}else{
	$tanggal_cari = date("Y-m-d");
	$date = date("YmdHis");
	$api_key = md5('XXXX'.$tanggal_cari.$date.'XXXX');
	$url = "https://api.fingerspot.io/api/download/attendance_log/XXXX/".$tanggal_cari."/11/date_time/asc/json/".$api_key."/".$date;
	$json = file_get_contents($url,false, stream_context_create($arrContextOptions));
	$obj = json_decode($json);
	$result = array();
	for ($i=0; $i <count($obj->data) ; $i++) {
		if($this->input->post('pencarian')==$obj->data[$i]->{'Type'}){
			array_push($result, array('nik'=>  $obj->data[$i]->{'NIK'},'nama' =>  $obj->data[$i]->{'Name'}, 'date_time' => $obj->data[$i]->{'Date Time'}, 'type' =>  $obj->data[$i]->{'Type'}  ) );
		}
	}
}


 echo json_encode(array("value"=>1,"result"=>$result));

 }

 public function pencarian_migrasi2()
 {
date_default_timezone_set("Asia/Jakarta");
$arrContextOptions=array(
	 "ssl"=>array(
			 "verify_peer"=>false,
			 "verify_peer_name"=>false,
	 ),
);
$tanggal_cari = $this->input->post('pencarian');
// $tanggal_cari = date('d-m-Y');
$date = date("YmdHis");
$api_key = md5('XXXX'.$tanggal_cari.$date.'XXXX');
$url = "https://api.fingerspot.io/api/download/attendance_log/XXXX/".$tanggal_cari."/11/date_time/asc/json/".$api_key."/".$date;
$json = file_get_contents($url,false, stream_context_create($arrContextOptions));
$obj = json_decode($json);

$result = array();
for ($i=0; $i <count($obj->data) ; $i++) {
	$cari_di_karyawan2 = $this->m_data->where('karyawan_2',array('nik' => $obj->data[$i]->{'NIK'}))->row();
	if(empty($cari_di_karyawan2)){}else{
		array_push($result, array('nik'=>  $obj->data[$i]->{'NIK'},'nama' =>  $cari_di_karyawan2->nama, 'date_time' => $obj->data[$i]->{'Date Time'}, 'type' =>  $obj->data[$i]->{'Type'}  ) );
	}
	// array_push($result, array('nik'=>  $obj->data[$i]->{'NIK'},'nama' =>  $obj->data[$i]->{'Name'}, 'date_time' => $obj->data[$i]->{'Date Time'}, 'type' =>  $obj->data[$i]->{'Type'}  ) );
}

echo json_encode($result);

}

	public function pencarian_migrasi()
	{
date_default_timezone_set("Asia/Jakarta");
$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);
$tanggal_cari = $this->input->post('pencarian');
 $date = date("YmdHis");
$api_key = md5('XXXX'.$tanggal_cari.$date.'XXXX');
 $url = "https://api.fingerspot.io/api/download/attendance_log/XXXX/".$tanggal_cari."/11/date_time/asc/json/".$api_key."/".$date;
$json = file_get_contents($url,false, stream_context_create($arrContextOptions));
$obj = json_decode($json);

$result = array();
for ($i=0; $i <count($obj->data) ; $i++) {
	array_push($result, array('nik'=>  $obj->data[$i]->{'NIK'},'nama' =>  $obj->data[$i]->{'Name'}, 'date_time' => $obj->data[$i]->{'Date Time'}, 'type' =>  $obj->data[$i]->{'Type'}  ) );
}

 echo json_encode(array("value"=>1,"result"=>$result));

 }

	public function migrasi_absen()
	{
date_default_timezone_set("Asia/Jakarta");
$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);
$tanggal_cari = $this->input->post('pencarian');
 $date = date("YmdHis");
$api_key = md5('XXXX'.$tanggal_cari.$date.'XXXX');
 $url = "https://api.fingerspot.io/api/download/attendance_log/XXXX/".$tanggal_cari."/6/date_time/asc/json/".$api_key."/".$date;
$json = file_get_contents($url,false, stream_context_create($arrContextOptions));
$obj = json_decode($json);

for ($i=0; $i <count($obj->data) ; $i++) {


				if(substr($obj->data[$i]->{'Date Time'},'11')>='15:00:00'){
					$status='Scan Out';
				}elseif(substr($obj->data[$i]->{'Date Time'},'11')<='09:00:00'){
					$status='Scan In';
				}else{
					$status=$obj->data[$i]->{'Type'};
				}


					$data =  array('nik'=>  $obj->data[$i]->{'NIK'},
													'nama_karyawan' =>  $obj->data[$i]->{'Name'},
													'date_time' => $obj->data[$i]->{'Date Time'},
													'status' => $status,
													'pin' =>  $obj->data[$i]->{'PIN'},

												  );
											$this->m_data->input_data($data,'absen');
}

 echo json_encode(array("value"=>1));

 }
	 public function cari_karyawan()
	 {
 	 	date_default_timezone_set("Asia/Jakarta");
 		$arrContextOptions=array(
		 "ssl"=>array(
				 "verify_peer"=>false,
				 "verify_peer_name"=>false,
		 ),
 	);
	$tanggal_cari = $this->input->post('pencarian');
	$date = date("YmdHis");
	$api_key = md5('XXXX'.$tanggal_cari.$date.'XXXX');
	$url = "https://api.fingerspot.io/api/download/attendance_log/XXXX/".$tanggal_cari."/11/date_time/asc/json/".$api_key."/".$date;
	$json = file_get_contents($url,false, stream_context_create($arrContextOptions));
	$obj = json_decode($json);

	$result = array();
	for ($i=0; $i <count($obj->data) ; $i++) {
	 if($obj->data[$i]->{'Type'}=='Scan In'){
		 $karyawan = $this->m_data->where('karyawan',array('nik' => $obj->data[$i]->{'NIK'}, ))->row();
		 if(empty($karyawan)){
			 array_push($result, array('nik'=>  $obj->data[$i]->{'NIK'},'nama' =>  $obj->data[$i]->{'Name'}, 'pin' => $obj->data[$i]->{'PIN'}, 'type' =>  $obj->data[$i]->{'Type'}  ) );
		 }else{

		 }
	}
 }
	echo json_encode(array("value"=>1,"result"=>$result));
	}



	public function list_karyawan()
	{
$karyawan = $this->m_data->semua('karyawan')->result();
$result = array();
foreach ($karyawan as $rec ) {
	array_push($result, array('nik'=>  $rec->nik ,'nama' =>  $rec->nama_karyawan ) );
}
  echo json_encode(array("value"=>1,"result"=>$result));
 }

 public function add_karyawan()
 {
 $data =  array('nik'=>  $this->input->post('nik'),
								 'nama_karyawan' =>   $this->input->post('nama'),
								 'pin' =>   $this->input->post('pin'),
								 'status' =>   'karyawan',
								 );
						 $this->m_data->input_data($data,'karyawan');
 echo json_encode(array("value"=>1));
 }

 public function cari_data_jam()
 {
	 if($this->input->post('pencarian')=='Masuk'){
		$status = 'Scan In';
	}elseif($this->input->post('pencarian')=='Pulang'){
		$status = 'Scan Out';
	}else{
		$status = $this->input->post('pencarian');
	}
	 $result = array();
	 if($this->input->post('pencarian')=='Izin'){
		 $karyawan = $this->m_data->where('absen',array('status' => $this->input->post('pencarian'),'nik' => $this->input->post('nik'),'nama_karyawan' => $this->input->post('nama') ))->result();
		 foreach ($karyawan as $rec ) {
			 array_push($result, array('nik'=>  $rec->nik ,'nama' =>  $rec->nama_karyawan,'date_time' => $rec->date_time, 'type' =>  $rec->status,'gambar_file' => $rec->gambar ) );
		 }
	 }else{
$karyawan = $this->m_data->where('data_jam', array('status' => $this->input->post('pencarian'), ))->result();
		 foreach ($karyawan as $rec ) {
				$jml = $this->m_data->jmlJam($status,$rec->jam,$this->input->post('nama'),$this->input->post('nik'),$this->input->post('tgl'))->row();
				if(empty($jml)){
					$tt_jam = 0;
				}else{
					$tt_jam = $jml->total;
				}
			 array_push($result, array('jam'=>  $rec->jam, 'jml_jam' =>  $tt_jam, 'tanggal_pencarian' => $this->input->post('tgl') ) );
		 }
	 }
	 if(empty($karyawan)){
		 echo json_encode(array("value"=>0));
	 }else{
		 echo json_encode(array("value"=>1,"result"=>$result));
	 }
 }

  public function list_jam()
  {
 	$list = $this->m_data->list_jam($this->input->post('status'),$this->input->post('jam'),$this->input->post('nama'),$this->input->post('nik'),$this->input->post('tanggal'))->result();
 	 $result = array();
 	 foreach ($list as $rec ) {
 		 array_push($result, array('date_time'=>  $rec->date_time ) );
 	 }
 	 if(empty($list)){
 		 echo json_encode(array("value"=>0));
 	 }else{
 		 echo json_encode(array("value"=>1,"result"=>$result));
 	 }
  }

  public function login()
  {
		$where = array('nik' => $this->input->post('nik'),
	 );
 	 $karyawan = $this->m_data->where('karyawan',$where )->row();

 	 if(empty($karyawan)){
 		 echo json_encode(array("value"=>0));
	  	 }else{
 		 echo json_encode(array("value"=>1,"id"=>$karyawan->id_karyawan,"nama_karyawan"=>$karyawan->nama_karyawan,"nik"=>$karyawan->nik,"status"=>$karyawan->status,"statusDivisi"=>$karyawan->level));
 	 }
  }

	public function pencarian_absen()
	{
		$where = array('nama_karyawan LIKE' => '%'.$this->input->post("nama").'%' );
		$karyawan = $this->m_data->where('karyawan',$where)->result();

		if(empty($karyawan)){
				echo json_encode(array("value"=>0));
		}else{
			$result = array();
			foreach ($karyawan as $rec ) {
				array_push($result, array('nik'=>  $rec->nik ,'nama' =>  $rec->nama_karyawan ) );
			}

			echo json_encode(array("value"=>1,"result"=>$result));
					}


	}



	public function upload()
	{

		$this->load->library('upload');
			$gpass = NULL;
				$n = 3; // jumlah karakter yang akan di bentuk.
				$chr = "0123456789";
				for ($i = 0; $i < $n; $i++) {
				$rIdx = rand(1, strlen($chr));
				$gpass .=substr($chr, $rIdx, 1);
				}
				if(empty($this->input->post('status_izin'))){
					$status_izin = "";
				}else{
					$status_izin = $this->input->post('status_izin');
				}
				$time = date("H:i:s");
			$nmfile = "img".$gpass."-" .time(); // deklarasi penamaan file img yg akan di upload
				$config[ 'upload_path' ] = './upload/'; // direktori uplad
				// $config[ 'encrypt_name' ] = TRUE;
				$config[ 'allowed_types' ] = 'jpg|png|jpeg'; // jenis extensi file img
				$config[ 'max_size' ] = 10000; // size foto
				$config[ 'file_name' ] = $nmfile; // config penamaan file img
				$this->upload->initialize( $config );
				if($_FILES['imageupload']['name']){ // jika input type file sudah ada inputan
						if ($this->upload->do_upload('imageupload')){ // upload foto
								$gbr = $this->upload->data(); // deklarasi upload foto
								$data = array(
									'nama_karyawan' =>  $this->input->post('nama_karyawan'),
									'nik'  => $this->input->post('nik'),
									'gambar'  => 'https://android.stikesbanyuwangi.ac.id/upload/'.$gbr['file_name'],
									'date_time' => $this->input->post('tanggal').' '.$time,
									'status' => 'Izin',
									'status_izin' => $status_izin,
								);
								$this->m_data->input_data($data,'absen');
							echo json_encode(array("value"=>1));
						}else{

						echo json_encode(array("value"=>9));
						}
					}else{

					echo json_encode(array("value"=>9));
					}
				}

			public function adddivisi() {
				$data =  array('divisi'=>  $this->input->post('divisi'),
												);
												$where = array('nik' =>  $this->input->post('nik'));
										$this->m_data->update_data($where,$data,'karyawan');
				echo json_encode(array("value"=>1));


			}

			public function persentase(){

				$persen_hadir = $this->m_data->persenhadir("60271008")->row();
				$persen_izin = $this->m_data->persenizin($this->input->post('nik'))->row();
				$all = $this->m_data->semua_absen()->row();

				 $persentase = round($persen_hadir->hasil/$all->hasil*100);

				echo json_encode(array("value"=>1,"persenHadir"=>$persen_hadir->hasil,"persenIzin"=>$persen_izin->hasil,"persentase"=>$persentase));

			}



}
