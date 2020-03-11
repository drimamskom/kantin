<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role {
    
        var $CI;
        private $menuid;
        
        public function __construct(){
                // Assign the CodeIgniter super-object
                $this->CI =& get_instance();
                $this->CI->load->helper('url');
                $this->CI->load->library('session');
                $this->CI->load->database();
        }
        
	public function getAkses(){  
            $current_uri = uri_string();   // untuk deteksi URL aktif  
            
            $sesi = $this->CI->session->get_userdata();
            $akses = $sesi['akses'];
            
            $query = $this->CI->db->query("SELECT m.id
                                        FROM tb_menu m
                                        LEFT JOIN tb_user_role ur ON ur.menu=m.id
                                        WHERE m.link!='#' AND aktif='1' AND m.link='$current_uri' 
                                        AND ur.akses='$akses' ");
            $row = $query->first_row();
            $jml = $query->num_rows();
            if($jml>0){
                $this->menuid = $row->id;
                return TRUE;
            }else{
                return FALSE;
            }
            
	}
        
	public function getPermission(){      
            $sesi = $this->CI->session->get_userdata();
            $akses = $sesi['akses'];
            
            $query = $this->db->query("SELECT permission FROM tb_akses_role WHERE akses='$akses' ");
            $jml = $query->num_rows();
            if($jml>0){
                return $query->result_array();
            }else{
                return FALSE;
            }
            
	}
        
	public function PermInsert(){   
            $sesi = $this->CI->session->get_userdata();
            $akses = $sesi['akses'];
            
            $query = $this->db->query("SELECT permission FROM tb_akses_role WHERE akses='$akses' AND permission='INSERT' ");
            $jml = $query->num_rows();
            if($jml>0){
                return TRUE;
            }else{
                return FALSE;
            }    
            
	}
        
	public function PermEdit(){      
            $sesi = $this->CI->session->get_userdata();
            $akses = $sesi['akses'];
            
            $query = $this->db->query("SELECT permission FROM tb_akses_role WHERE akses='$akses' AND permission='EDIT' ");
            $jml = $query->num_rows();
            if($jml>0){
                return TRUE;
            }else{
                return FALSE;
            }    
            
	}
        
	public function PermDelete(){     
            $sesi = $this->CI->session->get_userdata();
            $akses = $sesi['akses'];
            
            $query = $this->db->query("SELECT permission FROM tb_akses_role WHERE akses='$akses' AND permission='DELETE' ");
            $jml = $query->num_rows();
            if($jml>0){
                return TRUE;
            }else{
                return FALSE;
            }    
            
	}
        
}
