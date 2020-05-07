<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class P extends CI_Controller {

  private function number_of_working_days($from, $to) {
      $workingDays = [1, 2, 3, 4, 5]; # date format = N (1 = Monday, ...)
      $holidayDays = ['*-12-25', '*-01-01']; # variable and fixed holidays

      $from = new DateTime($from);
      $to = new DateTime($to);
      $to->modify('+1 day');
      $interval = new DateInterval('P1D');
      $periods = new DatePeriod($from, $interval, $to);

      $days = 0;
      foreach ($periods as $period) {
          if (!in_array($period->format('N'), $workingDays)) continue;
          if (in_array($period->format('Y-m-d'), $holidayDays)) continue;
          if (in_array($period->format('*-m-d'), $holidayDays)) continue;
          $days++;
      }
      return $days;
  }

  function okexx(){
    $list_kar = $this->m_data->semua('karyawan')->result();
    $s1 = date('d-m-Y', strtotime('18-03-2020' . ' + 1 day')); // Added one day to start from 03-05-2018
     $s2 = date('d-m-Y', strtotime('03-04-2020'.' + 1 day')); //Added one day to end with 08-05-2018
     $start = new DateTime($s1);
     $end   = new DateTime($s2);
     $interval = DateInterval::createFromDateString('1 day');
     $period   = new DatePeriod($start, $interval, $end);
    $no=0;
    foreach($list_kar as $l){
      if($l->nama_karyawan == 'Testerbosku'){

      }else{
        $no++;
        foreach ($period as $dt) {
          if($dt->format('l') == 'Saturday'){

          }elseif($dt->format('l') == 'Sunday'){

          }else{
            $data = array(
              'nama_karyawan' => $l->nama_karyawan,
              'nik' => $l->nik,
              'pin' => $l->pin,
              'date_time' => $dt->format('Y-m-d')." 08:00:00",
              'status' => 'Scan In',
            );
            $datax = array(
              'nama_karyawan' => $l->nama_karyawan,
              'nik' => $l->nik,
              'pin' => $l->pin,
              'date_time' => $dt->format('Y-m-d')." 15:00:00",
              'status' => 'Scan Out',
            );
            $this->db->insert('absen',$data);
            $this->db->insert('absen',$datax);
            echo $no.". ".$l->nama_karyawan."<br>";
            echo "<pre>".$dt->format("Y-m-d H:i:s");

          }
        }


      }
    }



  }

  public function migrasi_absen(){
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
    				if(substr($obj->data[$i]->{'Date Time'},'11')>='12:00:00'){
    					$status='Scan Out';
    				}elseif(substr($obj->data[$i]->{'Date Time'},'11')<='09:00:00'){
    					$status='Scan In';
    				}else{
    					$status=$obj->data[$i]->{'Type'};
    				}
            $where= array(
              'nik' => $obj->data[$i]->{'NIK'},
              'nama_karyawan' =>  $obj->data[$i]->{'Name'},
              'date_time' => $obj->data[$i]->{'Date Time'},
              'status' => $status,
            );
            $cari_di_absen = $this->m_data->where('absen',$where)->row();
            if($cari_di_absen){}else{
    					$data =  array('nik'=>  $obj->data[$i]->{'NIK'},
    													'nama_karyawan' =>  $obj->data[$i]->{'Name'},
    													'date_time' => $obj->data[$i]->{'Date Time'},
    													'status' => $status,
    													'pin' =>  $obj->data[$i]->{'PIN'},

    												  );
    											$this->m_data->input_data($data,'absen');
            }
    }

     echo json_encode(array("value"=>1));

  }


  public function absen_now(){
    date_default_timezone_set("Asia/Jakarta");
    $tgl = date("Y-m-d");
    $cari_karyawan=$this->m_data->where('karyawan',array('nik' => $this->input->post('nik')))->row();
    $cari_diabsen = $this->m_data->cek_absenz($this->input->post('nik'),$tgl,$this->input->post('status'))->row();
    if($cari_diabsen){
      echo json_encode(array("value"=>"gak"));
    }else{
    $data = array(
      'nama_karyawan' => $cari_karyawan->nama_karyawan,
      'nik' => $this->input->post('nik'),
      'pin' => $cari_karyawan->pin,
      'date_time' => date('Y-m-d H:i:s'),
      'status' => $this->input->post('status'),
    );
    $this->db->insert('absen',$data);
    echo json_encode(array("value"=>"kenek"));
  }

  }

  public function testerz(){
    $datalog = $this->m_data->semua('log')->result();
    $result=array();
    foreach($datalog as $r){
      array_push($result,array("id_user" => $r->id_user,"tanggal_kegiatan" => $r->tanggal_kegiatan,"waktu_kegiatan" => $r->waktu_kegiatan,"nama_kegiatan" => $r->nama_kegiatan,"uraian_kegiatan" => $r->uraian_kegiatan));
    }
    echo json_encode($result);
  }

  public function cek_cuti(){
    $cek_cuti = $this->m_data->cek_cuti($this->input->post('nik'))->row();
    echo json_encode(array("cuti" => $cek_cuti->cuti));
  }

  public function login(){
    $where = array('nik' => $this->input->post('nik'),
	 );
 	 $karyawan = $this->m_data->where('karyawan',$where )->row();
   $result=array();
 	 if(empty($karyawan)){
     array_push($result,array('stat' => 'kosong'));

 		 // echo json_encode(array('stat' => 'kosong'));
	  	 }else{
         array_push($result,array("id"=>$karyawan->id_karyawan,"nama_karyawan"=>$karyawan->nama_karyawan,"nik"=>$karyawan->nik,"status"=>$karyawan->status,"statusDivisi"=>$karyawan->level,'stat' => 'ada'));
 	 }
   echo json_encode($result);

  }

  public function cek_karyawan(){
    $where = array('nik' => $this->input->post('nik'),
	 );
 	 $karyawan = $this->m_data->where('karyawan',$where)->row();
   $result=array();
   array_push($result,array("id"=>$karyawan->id_karyawan,"nama_karyawan"=>$karyawan->nama_karyawan,"nik"=>$karyawan->nik,"status"=>$karyawan->status,"statusDivisi"=>$karyawan->level));
   echo json_encode($result);
  }

  public function mati_listrik(){
    $stat_mati = 0;
    if($stat_mati == 1){
      echo json_encode(array('listrik' => 'mati'));
    }else{
      echo json_encode(array('listrik' => 'murup'));
    }
  }

  public function mati_listrik1(){
    $stat_mati = 1;
    if($stat_mati == 1){
      echo json_encode(array('listrik' => 'mati'));
    }else{
      echo json_encode(array('listrik' => 'murup'));
    }
  }

  public function update_data_device(){
    $where = array('nik' => $this->input->post('nik'),
	 );
 	 $karyawan = $this->m_data->where('karyawan',$where)->row();
   $data = array(
     'model_hp' => $this->input->post('model_hp'),
     'android_ver' => $this->input->post('android_ver'),
     'android_api' => $this->input->post('android_api'),
     'device_width' => $this->input->post('device_width'),
     'device_height' => $this->input->post('device_height'),
     'device_product' => $this->input->post('device_product'),
     'device_fracture' => $this->input->post('device_fracture'),
   );
   $this->m_data->update_data(array('id_karyawan' => $karyawan->id_karyawan),$data,'karyawan');
  }

  public function update_data_device1(){
    $where = array('nik' => $this->input->post('nik'),
	 );
 	 $karyawan = $this->m_data->where('karyawan',$where)->row();
   $data = array(
     'model_hp' => $this->input->post('model_hp'),
     'device_fracture' => $this->input->post('device_fracture'),
   );
   $this->m_data->update_data(array('id_karyawan' => $karyawan->id_karyawan),$data,'karyawan');
  }

  public function rekap_alpa(){
    date_default_timezone_set("Asia/Jakarta");
    if(empty($this->input->post('tanggal'))){
      $date = date("m");
      $absen_izin = $this->m_data->where('absen',array('nik' => $this->input->post('nik'),'status' => 'Izin','MONTH(date_time)' => $date ))->result();
    }else{
      $date = $this->input->post('tanggal');
      $absen_izin = $this->m_data->where('absen',array('nik' => $this->input->post('nik'),'status' => 'Izin','date_time >' => $date ))->result();
    }

      $result = array();
      foreach ($absen_izin as $rec ) {
        array_push($result, array('nik'=>  $rec->nik ,'nama' =>  $rec->nama_karyawan,'date_time' => date('d-m-Y H:i:s',strtotime($rec->date_time)), 'type' =>  $rec->status,'gambar_file' => $rec->gambar,'status_izinx' => $rec->status_izin ) );
      }

    echo json_encode($result);

    // }
  }

  public function add_karyawan(){
  $data =  array('nik'=>  $this->input->post('nik'),
 								 'nama_karyawan' =>  $this->input->post('nama'),
 								 'pin' =>   $this->input->post('pin'),
 								 'status' =>   'karyawan',
                 'cuti' => '12'
 								 );
                 $data1 = array(
                   'nama' => $this->input->post('nama'),
                   'nik'=>  $this->input->post('nik'),
                 );
                 $this->db->insert('karyawan',$data);
                 $cari_karyawan2 = $this->m_data->cari_karyawan_like($this->input->post('nama'))->row();
                 if($cari_karyawan2){}else{
                   $this->db->insert('karyawan_2',$data1);
           }
  echo json_encode(array("value"=>1));
  }

  public function add_karyawanx(){
    date_default_timezone_set("Asia/Jakarta");
    $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );
    if(empty($this->input->post('tanggal'))){
      $tanggal_cari = date("Y-m-d");
    }else{
      $tanggal_cari = $this->input->post('tanggal');
    }
    $date = date("YmdHis");
    $api_key = md5('XXXX'.$tanggal_cari.$date.'XXXX');
    $url = "https://api.fingerspot.io/api/download/attendance_log/XXXX/".$tanggal_cari."/11/date_time/asc/json/".$api_key."/".$date;
    $json = file_get_contents($url,false, stream_context_create($arrContextOptions));
    $obj = json_decode($json);
    $result = array();
    for ($i=0; $i <count($obj->data) ; $i++) {
      $cari_karyawan = $this->m_data->where('karyawan',array('nama_karyawan' => $obj->data[$i]->{'Name'}))->row();
      if($cari_karyawan){
        // echo $obj->data[$i]->{'Name'}."<br>";
      }else{
        // echo $obj->data[$i]->{'Name'}."<br>";
        array_push($result, array('nik'=>  $obj->data[$i]->{'NIK'},'nama' =>  $obj->data[$i]->{'Name'}, 'date_time' => $obj->data[$i]->{'Date Time'}, 'type' =>  $obj->data[$i]->{'Type'}  ) );
      }
    }
    echo json_encode($result);
  }

  public function absen_hari_ini(){
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
        // if(substr($obj->data[$i]->{'Date Time'},'11')>='12:00:00'){
        //   $status='Scan Out';
        // }elseif(substr($obj->data[$i]->{'Date Time'},'11')<='12:00:00'){
        //   $status='Scan In';
        // }else{
        //   $status=$obj->data[$i]->{'Type'};
        // }
        if($this->input->post('pencarian')==$obj->data[$i]->{'Type'}){
          array_push($result, array('nik'=>  $obj->data[$i]->{'NIK'},'nama' =>  $obj->data[$i]->{'Name'}, 'date_time' => $obj->data[$i]->{'Date Time'},
          'type' =>  $obj->data[$i]->{'Type'},
          "hours" => date("H",strtotime($obj->data[$i]->{'Date Time'})),
          "min" => date("i",strtotime($obj->data[$i]->{'Date Time'})),
          ) );
        }
      }
    }

     echo json_encode($result);

  }

  public function absen_hari_ini22(){
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
          array_push($result, array('nik'=>  $rec->nik ,'nama' =>  $rec->nama_karyawan,
          'date_time' => $rec->date_time, 'type' =>  $rec->status,
          'gambar_file' => $rec->gambar,
          "hours" => date("H",strtotime($rec->date_time)),
          "min" => date("i",strtotime($rec->date_time)),
         ) );
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
      $get_absen = $this->m_data->where('absen',array('nik' => '000999000','date(date_time)' => '2020-02-25'))->result();
      foreach($get_absen as $g){
        if($this->input->post('pencarian') == $g->status){
          if($this->input->post('nik') == $g->nik){
            array_push($result, array('nik'=>  $g->nik,'nama' =>  $g->nama_karyawan, 'date_time' => $g->date_time,
            'type' =>  $g->status,
            "hours" => date("H",strtotime($g->date_time)),
            "min" => date("i",strtotime($g->date_time)),
            ) );
          }
        }
      }

    }
     echo json_encode($result);

  }

  public function absen_hari_ini21(){
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
          array_push($result, array('nik'=>  $rec->nik ,'nama' =>  $rec->nama_karyawan,
          'date_time' => $rec->date_time, 'type' =>  $rec->status,
          'gambar_file' => $rec->gambar,
          "hours" => date("H",strtotime($rec->date_time)),
          "min" => date("i",strtotime($rec->date_time)),
         ) );
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
        // if($this->input->post('pencarian') == $obj->data[$i]->{'Type'}){
          if($this->input->post('nik') == $obj->data[$i]->{'NIK'}){
            array_push($result, array('nik'=>  $obj->data[$i]->{'NIK'},'nama' =>  $obj->data[$i]->{'Name'}, 'date_time' => $obj->data[$i]->{'Date Time'},
            'type' =>  $obj->data[$i]->{'Type'},
            "hours" => date("H",strtotime($obj->data[$i]->{'Date Time'})),
            "min" => date("i",strtotime($obj->data[$i]->{'Date Time'})),
            ) );
          }
        // }
      }
    }

     echo json_encode($result);

  }

  public function absen_hari_ini2(){
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
          array_push($result, array('nik'=>  $rec->nik ,'nama' =>  $rec->nama_karyawan,
          'date_time' => $rec->date_time, 'type' =>  $rec->status,
          'gambar_file' => $rec->gambar,
          "hours" => date("H",strtotime($rec->date_time)),
          "min" => date("i",strtotime($rec->date_time)),
         ) );
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
        if($this->input->post('pencarian') == $obj->data[$i]->{'Type'}){
          if($this->input->post('nik') == $obj->data[$i]->{'NIK'}){
            array_push($result, array('nik'=>  $obj->data[$i]->{'NIK'},'nama' =>  $obj->data[$i]->{'Name'}, 'date_time' => $obj->data[$i]->{'Date Time'},
            'type' =>  $obj->data[$i]->{'Type'},
            "hours" => date("H",strtotime($obj->data[$i]->{'Date Time'})),
            "min" => date("i",strtotime($obj->data[$i]->{'Date Time'})),
            ) );
          }
        }
      }
    }

     echo json_encode($result);

  }



  public function list_jamxy(){
    $list = $this->m_data->list_jamya($this->input->post('nik'),$this->input->post('jam'),$this->input->post('stat'),$this->input->post('tanggal'))->result();
    $result = array();
    foreach ($list as $rec ) {
      $tanggal = tgl_indo(date('Y-m-d', strtotime($rec->date_time)));
      array_push($result, array('tanggal'=>  $tanggal,'status' => $rec->status,'nik' => $rec->nik));
    }
    echo json_encode($result);
  }

  public function list_jam(){
    $list = $this->m_data->list_jamx($this->input->post('nik'),$this->input->post('stat1'),$this->input->post('stat2'),$this->input->post('tanggal'))->result();
    if(empty($this->input->post('tanggal'))){
      $date = date('Y-m-d');
    }else{
      $date = $this->input->post('tanggal');
    }
     $result = array();
   	 foreach ($list as $rec ) {
   		 array_push($result, array('jam'=>  $rec->jam,'jml' => $rec->jml,'status' => $rec->status,'tanggal' => $date));
   	 }
     echo json_encode($result);
  }

  public function ijinx(){
    $cari_data_nik = $this->m_data->where('karyawan',array('nik' => $this->input->post('nik')))->row();
    $image = $_POST['image'];
	  $name = $_POST['name'];

	 $realImage = base64_decode($image);
	 $dir = "./upload/";
	  $data = array(
        'nama_karyawan' => $cari_data_nik->nama_karyawan,
        'nik' => $cari_data_nik->nik,
        'pin' => $cari_data_nik->pin,
        'date_time' => $this->input->post('tanggal'),
        'status' => 'Izin',
        'gambar' => base_url('upload/').$name,
        'status_izin' => $this->input->post('status_izin'),
			);
			$this->db->insert('absen',$data);
	 file_put_contents($dir.$name, $realImage);
   if($this->input->post('status_izin') == 'Izin Pribadi' || $this->input->post('status_izin') == 'Cuti'){
    $kurang_cuti = $cari_data_nik->cuti - 1;
     $datay = array(
       'cuti' => $kurang_cuti
     );
     $this->m_data->update_data(array('id_karyawan' => $cari_data_nik->id_karyawan),$datay,'karyawan');
   }
   // $kurang_cuti = $cari_data_nik->cuti - 1;
	 echo "Data Berhasil Masuk";
  }

  function okex(){
    $day1='2020-02-24';
    $day2='2020-02-28';
    $begin = new DateTime($day1);
$end = new DateTime($day2);

$interval = DateInterval::createFromDateString('1 day');
$period = new DatePeriod($begin, $interval, $end);
foreach ($period as $dt) {
 echo $dt->format("Y-m-d H:i:s")."<br>";
}
  }

  public function ijinx2(){
    $cari_data_nik = $this->m_data->where('karyawan',array('nik' => $this->input->post('nik')))->row();
    $image = $_POST['image'];
    $name = $_POST['name'];

   $realImage = base64_decode($image);
   $dir = "./upload/";

       if($this->input->post('status_izin') == 'Izin Pribadi' || $this->input->post('status_izin') == 'Cuti'){
         $datetime1 = new DateTime($this->input->post('tanggal'));
         $datetime2 = new DateTime($this->input->post('tanggal2'));
         $day1=$this->input->post('tanggal');
         $day2=$this->input->post('tanggal2');

         if(nama_hari(date($day1)) == 'Sabtu'){
         $new_day1=date('Y-m-d', strtotime($day1 . " +2 days"));
         }elseif(nama_hari(date($day1)) == 'Minggu'){
         $new_day1=date('Y-m-d', strtotime($day1 . " +1 days"));
         }else{
         $new_day1=$day1;
         }

         if(nama_hari(date($day2)) == 'Sabtu'){
         $new_day2=date('Y-m-d', strtotime($day2 . " -2 days"));
         }elseif(nama_hari(date($day2)) == 'Minggu'){
         $new_day2=date('Y-m-d', strtotime($day2 . " -1 days"));
         }else{
           $new_day2=$day2;
         }
         $daysf = $this->number_of_working_days($new_day1, $new_day2);
         $total_izin = $cari_data_nik->cuti - $daysf;

         $datay = array(
           'cuti' => $total_izin
         );
         $this->m_data->update_data(array('id_karyawan' => $cari_data_nik->id_karyawan),$datay,'karyawan');
       }else{
         $datetime1 = new DateTime($this->input->post('tanggal'));
         $datetime2 = new DateTime($this->input->post('tanggal2'));
         $day1=$this->input->post('tanggal');
         $day2=$this->input->post('tanggal2');

         if(nama_hari(date($day1)) == 'Sabtu'){
         $new_day1=date('Y-m-d', strtotime($day1 . " +2 days"));
         }elseif(nama_hari(date($day1)) == 'Minggu'){
         $new_day1=date('Y-m-d', strtotime($day1 . " +1 days"));
         }else{
         $new_day1=$day1;
         }

         if(nama_hari(date($day2)) == 'Sabtu'){
         $new_day2=date('Y-m-d', strtotime($day2 . " -2 days"));
         }elseif(nama_hari(date($day2)) == 'Minggu'){
         $new_day2=date('Y-m-d', strtotime($day2 . " -1 days"));
         }else{
           $new_day2=$day2;
         }
         $daysf = $this->number_of_working_days($new_day1, $new_day2);
       }
       $begin = new DateTime($day1);
   $end = new DateTime(date('Y-m-d', strtotime($day2 . " +1 days")));

   $interval = DateInterval::createFromDateString('1 day');
   $period = new DatePeriod($begin, $interval, $end);
   foreach ($period as $dt) {
     $data = array(
         'nama_karyawan' => $cari_data_nik->nama_karyawan,
         'nik' => $cari_data_nik->nik,
         'pin' => $cari_data_nik->pin,
         'date_time' => $dt->format("Y-m-d H:i:s"),
         'date_izin' => $this->input->post('tanggal2'),
         'status' => 'Izin',
         'gambar' => base_url('upload/').$name,
         'status_izin' => $this->input->post('status_izin'),
         // 'durasi_izin' => $daysf
         'durasi_izin' => '1'
       );
     $this->db->insert('absen',$data);
    // echo $dt->format("Y-m-d H:i:s")."<br>";
   }

    file_put_contents($dir.$name, $realImage);
   // echo $daysf;
   echo "Data Berhasil Masuk";
  }

  function yaa(){
$datetime1 = new DateTime('2020-01-28');
$datetime2 = new DateTime('2020-02-08');
$day1="2020-01-28";
$day2="2020-02-08";
  if(nama_hari(date($day1)) == 'Sabtu'){
  $new_day1=date('Y-m-d', strtotime($day1 . " -2 days"));
  }elseif(nama_hari(date($day1)) == 'Minggu'){
  $new_day1=date('Y-m-d', strtotime($day1 . " -1 days"));
  }else{
  $new_day1=$day1;
  }

  if(nama_hari(date($day2)) == 'Sabtu'){
  $new_day2=date('Y-m-d', strtotime($day2 . " -2 days"));
  }elseif(nama_hari(date($day2)) == 'Minggu'){
  $new_day2=date('Y-m-d', strtotime($day2 . " -1 days"));
  }else{
    $new_day2=$day2;
  }

$daysf = $this->number_of_working_days($new_day1, $new_day2);

echo $daysf;
  }

  public function cari_telat(){
    $cek_telat = $this->m_data->cek_telat($this->input->post('nik'),date('Y-m-d H:i:s',strtotime($this->input->post('tanggal'))),'Scan In')->row();
    if($cek_telat){
      echo json_encode(array('stat' => 'yes','alasan' => $cek_telat->alasan_telat,'gambar' => $cek_telat->gambar));
    }else{
      echo json_encode(array('stat' => 'no','alasan' => 'Alasan belum terisi','gambar' => ''));
    }
  }

  public function cari_pulang(){
    $cek_pulang = $this->m_data->cek_muleh($this->input->post('nik'),date('Y-m-d H:i:s',strtotime($this->input->post('tanggal'))),'Scan Out')->row();
    if($cek_pulang){
      echo json_encode(array('stat' => 'yes','alasan' => $cek_pulang->alasan_pulang,'gambar' => $cek_pulang->gambar));
    }else{
      echo json_encode(array('stat' => 'no','alasan' => 'Alasan belum terisi','gambar' => ''));
    }
  }

  public function cek_telat(){
    $cek_telat = $this->m_data->cek_telat($this->input->post('nik'),date('Y-m-d H:i:s',strtotime($this->input->post('tanggal'))),'Scan In')->row();
    if($cek_telat){
      echo json_encode(array('stat' => 'no'));
    }else{
      echo json_encode(array('stat' => 'yes'));
    }
  }

  public function cek_pulangx(){
    $cek_pulang = $this->m_data->cek_muleh($this->input->post('nik'),date('Y-m-d H:i:s',strtotime($this->input->post('tanggal'))),'Scan Out')->row();
    if($cek_pulang){
      echo json_encode(array('stat' => 'no'));
    }else{
      echo json_encode(array('stat' => 'yes'));
    }
  }

  public function form_telatx(){
    $cari_karyawan = $this->m_data->where('karyawan',array('nik' => $this->input->post('nik')))->row();
    $cek_diabsen = $this->m_data->where('absen',array('nik' => $this->input->post('nik'),'date_time' => date('Y-m-d H:i:s',strtotime($this->input->post('tanggal'))),'status' => 'Scan In' ))->row();
    $image = $_POST['image'];
    $name = $_POST['name'];

    $realImage = base64_decode($image);
    $dir = "./upload/";
    if($cek_diabsen){
      // update
      $data = array(
        'alasan_telat' => $this->input->post('alasan'),
        'gambar' => base_url('upload/').$name,
      );
      $this->m_data->update_data(array('id_absen' => $cek_diabsen->id_absen),$data,'absen');
    }else{
      // insert
      $data = array(
        'nama_karyawan' => $cari_karyawan->nama_karyawan,
        'nik' => $this->input->post('nik'),
        'pin' => $cari_karyawan->pin,
        'date_time' => date('Y-m-d H:i:s',strtotime($this->input->post('tanggal'))),
        'status' => 'Scan In',
        'alasan_telat' => $this->input->post('alasan'),
        'gambar' => base_url('upload/').$name,
      );
      $this->db->insert('absen',$data);
    }
    file_put_contents($dir.$name, $realImage);
    echo "Data Berhasil Masuk";
    // echo json_encode(array("value"=>1));
  }

  public function form_cepatx(){
    $cari_karyawan = $this->m_data->where('karyawan',array('nik' => $this->input->post('nik')))->row();
    $cek_diabsen = $this->m_data->where('absen',array('nik' => $this->input->post('nik'),'date_time' => date('Y-m-d H:i:s',strtotime($this->input->post('tanggal'))),'status' => 'Scan Out' ))->row();
    $image = $_POST['image'];
    $name = $_POST['name'];

    $realImage = base64_decode($image);
    $dir = "./upload/";

    if($cek_diabsen){
      // update
      $data = array(
        'alasan_pulang' => $this->input->post('alasan'),
        'gambar' => base_url('upload/').$name,
      );
      $this->m_data->update_data(array('id_absen' => $cek_diabsen->id_absen),$data,'absen');
    }else{
      // insert
      $data = array(
        'nama_karyawan' => $cari_karyawan->nama_karyawan,
        'nik' => $this->input->post('nik'),
        'pin' => $cari_karyawan->pin,
        'date_time' => date('Y-m-d H:i:s',strtotime($this->input->post('tanggal'))),
        'status' => 'Scan Out',
        'alasan_pulang' => $this->input->post('alasan'),
        'gambar' => base_url('upload/').$name,
      );
      $this->db->insert('absen',$data);
    }
    file_put_contents($dir.$name, $realImage);
    echo "Data Berhasil Masuk";
    // echo json_encode(array("value"=>1));
  }

  public function presentase(){
    $result=array();
    $thn = date('Y');
    $bln = date('m');
    $tgln=date('d');
    $days=cal_days_in_month(CAL_GREGORIAN,$bln,$thn);
    $daysf = $this->number_of_working_days("$thn-$bln-01", "$thn-$bln-$days");
    $daysCN = $this->number_of_working_days("$thn-$bln-01", "$thn-$bln-$tgln");
    $scanIn = $this->m_data->jum_nik_scanIn($this->input->post('nik'),$thn,$bln,'Scan In')->num_rows();
    $jum_izin = $this->m_data->jum_nik_kar1($this->input->post('nik'),$thn,$bln,'Izin','Izin Pribadi')->num_rows();
    $jum_izin1 = $this->m_data->jum_nik_kar11($this->input->post('nik'),$thn,'Izin','Izin Pribadi')->num_rows();
    $jum_dl = $this->m_data->jum_nik_kar1($this->input->post('nik'),$thn,$bln,'Izin','Dinas Luar')->num_rows();
    $jum_bimbingan = $this->m_data->jum_nik_kar1($this->input->post('nik'),$thn,$bln,'Izin','Bimbingan')->num_rows();
    $jum_cuti = $this->m_data->jum_nik_kar1($this->input->post('nik'),$thn,$bln,'Izin','Cuti')->num_rows();
    $jum_izin_dokter = $this->m_data->jum_nik_kar1($this->input->post('nik'),$thn,$bln,'Izin','Surat Dokter')->num_rows();
    $alpa = $daysCN-$scanIn;
    // $jum_bimbingan = $this->m_data->jum_nik_kar('70000',$thn,$bln,'Izin Pribadi')->num_rows();
    // $jum_bimbingan = $this->m_data->jum_nik_kar('70000',$thn,$bln,'')->num_rows();
    $hadir = $scanIn;
    $hitung_persentasi = round(($daysCN-($jum_izin+$jum_dl+$jum_bimbingan+$jum_cuti+$jum_izin_dokter+$alpa))/$daysf*100);
    // $hitung_persentasi = round(($daysf-($jum_izin+$jum_dl+$jum_bimbingan+$jum_cuti+$jum_izin_dokter+$alpa))/$daysf*100);
    array_push($result,array('hadir' => $hadir,'izin' => $jum_izin1,'persen' => $hitung_persentasi));
    echo json_encode($result);
    // echo $daysf."(hari dibulan $bln)<br>";
    // echo $daysCN."(total hari sekarang)<br>";
    // echo $jum_izin." (izin) <br>";
    // echo $daysCN-$scanIn." (tidak masuk) <br>";

    // $jum_sakit = $this->m_data->jum_nik_kar('601060316',$thn,$bln,'Sakit')->num_rows();
  }

  function haversineGreatCircleDistance(
    $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
  {
    // convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
      cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
  }

  private function getDistance($latitude1, $longitude1, $latitude2, $longitude2) {
      $earth_radius = 6371;

      $dLat = deg2rad($latitude2 - $latitude1);
      $dLon = deg2rad($longitude2 - $longitude1);

      $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon/2) * sin($dLon/2);
      $c = 2 * asin(sqrt($a));
      $d = $earth_radius * $c;

      return $d;
  }

  function distance($lat1, $lon1, $lat2, $lon2, $unit) {
  if (($lat1 == $lat2) && ($lon1 == $lon2)) {
    return 0;
  }
  else {
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    if ($unit == "K") {
      return ($miles * 1.609344);
    } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
      return $miles;
    }
  }
}

function getDistance1($latitude1, $longitude1, $latitude2, $longitude2) {

    $earth_radius = 6371;

    $dLat = deg2rad($latitude2 - $latitude1);
    $dLon = deg2rad($longitude2 - $longitude1);

    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * asin(sqrt($a));
    $d = $earth_radius * $c;

    return $d;

}

public function vincentyGreatCircleDistance(
  $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
{
  // convert from degrees to radians
  $latFrom = deg2rad($latitudeFrom);
  $lonFrom = deg2rad($longitudeFrom);
  $latTo = deg2rad($latitudeTo);
  $lonTo = deg2rad($longitudeTo);

  $lonDelta = $lonTo - $lonFrom;
  $a = pow(cos($latTo) * sin($lonDelta), 2) +
    pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
  $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

  $angle = atan2(sqrt($a), $b);
  return $angle * $earthRadius;
}

  public function jarak(){
    $ok= $this->vincentyGreatCircleDistance($this->input->post('lat'),$this->input->post('lang'),-8.20695186,114.35996056);
    // $ya = $ok * 1000;
    // if(ceil($ok) > 1000){
    //   $ya = number_format(ceil($ok) / 1000,2);
    // }else{
    //   $ya = ceil($ok);
    // }
    echo json_encode(array('jarak' => $ok));
  }

  public function presentase1(){
    $result=array();
    $thn = date('Y');
    $bln = date('m');
    $tgln=date('d');
    $days=cal_days_in_month(CAL_GREGORIAN,$bln,$thn);
    $daysf = $this->number_of_working_days("$thn-$bln-01", "$thn-$bln-$days");
    $daysCN = $this->number_of_working_days("$thn-$bln-01", "$thn-$bln-$tgln");
    $scanIn = $this->m_data->jum_nik_scanIn('60161207',$thn,$bln,'Scan In')->num_rows();
    $jum_izin = $this->m_data->jum_nik_kar1('60161207',$thn,$bln,'Izin','Izin Pribadi')->num_rows();
    $jum_izin1 = $this->m_data->jum_nik_kar11('60161207',$thn,'Izin','Izin Pribadi')->num_rows();
    $jum_dl = $this->m_data->jum_nik_kar1('60161207',$thn,$bln,'Izin','Dinas Luar')->num_rows();
    $jum_bimbingan = $this->m_data->jum_nik_kar1('60161207',$thn,$bln,'Izin','Bimbingan')->num_rows();
    $jum_cuti = $this->m_data->jum_nik_kar1('60161207',$thn,$bln,'Izin','Cuti')->num_rows();
    $jum_izin_dokter = $this->m_data->jum_nik_kar1('60161207',$thn,$bln,'Izin','Surat Dokter')->num_rows();
    $alpa = $daysCN-$scanIn;
    // $jum_bimbingan = $this->m_data->jum_nik_kar('70000',$thn,$bln,'Izin Pribadi')->num_rows();
    // $jum_bimbingan = $this->m_data->jum_nik_kar('70000',$thn,$bln,'')->num_rows();
    // $hadir = $scanIn;
    // $hitung_persentasi = round(($daysCN-($jum_izin+$jum_dl+$jum_bimbingan+$jum_cuti+$jum_izin_dokter+$alpa))/$daysf*100);
    // // $hitung_persentasi = round(($daysf-($jum_izin+$jum_dl+$jum_bimbingan+$jum_cuti+$jum_izin_dokter+$alpa))/$daysf*100);
    // array_push($result,array('hadir' => $hadir,'izin' => $jum_izin1,'persen' => $hitung_persentasi));
    // echo json_encode($result);
    echo $jum_izin1;
    // echo $daysf."(hari dibulan $bln)<br>";
    // echo $daysCN."(total hari sekarang)<br>";
    // echo $jum_izin." (izin) <br>";
    // echo $daysCN-$scanIn." (tidak masuk) <br>";

    // $jum_sakit = $this->m_data->jum_nik_kar('601060316',$thn,$bln,'Sakit')->num_rows();
  }

}
