<?php
defined('BASEPATH') OR exit('No direct script access allowed');

	class M_data extends CI_Model{
		function input_data($data,$table){
            $this->db->insert($table,$data);
        }
        function hapus_data($where,$table){
            $this->db->where($where);
            $this->db->delete($table);
        }
        function update_data($where,$data,$table){
            $this->db->where($where);
            $this->db->where($where);
            $this->db->update($table,$data);
        }
        function semua($table){
            return $this->db->get($table);
        }
        function where($table,$where){
            return $this->db->get_where($table,$where);
        }

				function cek_telat($nik,$tgl,$status){
					$this->db->where('nik',$nik);
					$this->db->where('date_time',$tgl);
					$this->db->where('status',$status);
					$this->db->where('alasan_telat !=','');
					$query= $this->db->get('absen');
					return $query;
				}

				function cek_muleh($nik,$tgl,$status){
					$this->db->where('nik',$nik);
					$this->db->where('date_time',$tgl);
					$this->db->where('status',$status);
					$this->db->where('alasan_pulang !=','');
					$query= $this->db->get('absen');
					return $query;
				}

				function cek_absenz($nik,$tgl,$status){
					$this->db->where('nik',$nik);
					$this->db->where('DATE(date_time)',$tgl);
					$this->db->where('status',$status);
					$query = $this->db->get('absen');
					return $query;
				}

				function cari_karyawan_like($like){
					$this->db->like('nama',$like);
					$query = $this->db->get('karyawan_2');
					return $query;
				}


        function ordernya($name,$or,$table){
            $this->db->order_by($name, $or);
            $this->db->limit(1);
            $query = $this->db->get($table);
            return $query;
        }

        function jmlJam($status,$jam,$nama,$nik,$tgl){
            $this->db->select('count(id_absen) as total');
            $this->db->where('status',$status);
            $this->db->where('nama_karyawan',$nama);
            $this->db->where('nik',$nik);
            $this->db->LIKE('date_time',$jam);
            $this->db->LIKE('date_time',$tgl);
            $query = $this->db->get('absen');
            return $query;
        }

        function list_jam($status,$jam,$nama,$nik,$tanggal){
            $this->db->select('*');
            $this->db->where('status',$status);
						$this->db->where('nama_karyawan',$nama);
	          $this->db->where('nik',$nik);
            $this->db->LIKE('date_time',$jam);
            $this->db->LIKE('date_time',$tanggal);
            $query = $this->db->get('absen');
            return $query;
        }
        function cariDataIzin($tanggal,$nik){
            $this->db->select('*');
            $this->db->where('status','Izin');
						$this->db->where('nik',$nik);
            $this->db->LIKE('date_time',$tanggal);
            $query = $this->db->get('absen');
            return $query;
        }

				function persenhadir($nik){
					$this->db->select('Count(Distinct CAST(date_time as Date)) as hasil');
					$this->db->where('status !=','Izin');
					$this->db->where('nik',$nik);
					$query = $this->db->get('absen');
					return $query;
				}

				function persenizin($nik){
					$this->db->select('Count(Distinct CAST(date_time as Date)) as hasil');
					$this->db->where('status =','Izin');
					$this->db->where('nik',$nik);
					$query = $this->db->get('absen');
					return $query;
				}
				function semua_absen(){
					$this->db->select('Count(Distinct CAST(date_time as Date)) as hasil');
					$this->db->where('status !=','Izin');
					$query = $this->db->get('absen');
					return $query;
				}

				function jum_nik_kar1($nik,$thn,$bln,$status,$stat_izin){
					$this->db->distinct();
					$this->db->select('DATE(date_time) as date');
					$this->db->where('nik',$nik);
					$this->db->where('MONTH(date_time)',$bln);
					$this->db->where('YEAR(date_time)',$thn);
					$this->db->where('status',$status);
					$this->db->where('status_izin',$stat_izin);
					$query = $this->db->get('absen');
					return $query;
				}

				function jum_nik_kar11($nik,$thn,$status,$stat_izin){
					$this->db->distinct();
					$this->db->select('DATE(date_time) as date');
					$this->db->where('nik',$nik);
					$this->db->where('YEAR(date_time)',$thn);
					$this->db->where('status',$status);
					$this->db->where('status_izin',$stat_izin);
					$query = $this->db->get('absen');
					return $query;
				}

				function jum_nik_kar($nik,$thn,$bln,$status){
					$this->db->distinct();
					$this->db->select('DATE(date_time) as date');
					$this->db->where('nik',$nik);
					$this->db->where('MONTH(date_time)',$bln);
					$this->db->where('YEAR(date_time)',$thn);
					$this->db->where('status',$status);
					$query = $this->db->get('absen');
					return $query;
				}

				function list_jam_custom($nik,$stat1,$stat2){
					return $this->db->query("SELECT id_jam,jam,status,(SELECT COUNT(*)
					FROM absen
					where nik=''.$nik.''
					and hour(date_time) = jam and status=''.$stat2.'')
					as jml
					FROM data_jam2
					where status=''.$stat1n.''");
				}

				function list_jamya($nik,$jam,$stat,$tgl=null){
					$this->db->select('nik,date_time,status');
					$this->db->where('nik',$nik);
					if(is_null($tgl)){}else{
						$this->db->where('DATE(date_time)',$tgl);
					}
					$this->db->where('HOUR(date_time)',$jam);
					$this->db->where('status',$stat);
					$query = $this->db->get('absen');
					return $query;
				}

				function cek_cuti($nik){
					$this->db->select('nik,cuti');
					$this->db->where('nik',$nik);
					$query = $this->db->get('karyawan');
					return $query;
				}

				function list_jamx($nik,$stat1,$stat2,$tgl=null){
					if(is_null($tgl)){
						$set=date('m');
						$this->db->select('id_jam,jam,status,(SELECT COUNT(*) FROM absen where nik ="'.$nik.'" and MONTH(date_time) = "'.$set.'" and hour(date_time) = jam and status="'.$stat2.'") as jml');
					}else{
						$this->db->select('id_jam,jam,status,(SELECT COUNT(*) FROM absen where nik ="'.$nik.'" and DATE(date_time) = "'.$tgl.'" and hour(date_time) = jam and status="'.$stat2.'") as jml');
					}
					$this->db->where('status',$stat1);
					return $query = $this->db->get('data_jam2');
				}

				function jum_nik_scanIn($nik,$thn,$bln,$status){
					$this->db->distinct();
					$this->db->select('DATE(date_time) as date');
					$this->db->where('nik',$nik);
					$this->db->where('MONTH(date_time)',$bln);
					$this->db->where('YEAR(date_time)',$thn);
					$this->db->where('status',$status);
					$query = $this->db->get('absen');
					return $query;
				}







    }
