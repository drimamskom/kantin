<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {
    
	function __construct(){
            parent::__construct();          
	}
        
	public function index(){ 
            if($this->session->userdata('status') != "mart"){
                $this->session->sess_destroy();
                redirect(base_url('login'));
            }else{
                if($this->session->userdata('akses') == "customer"){
                    redirect(base_url('kantin'));
                }else if($this->session->userdata('akses') == "kantin"){
                    redirect(base_url('supplierinfo'));
                }else if($this->session->userdata('akses') == "kasir"){
                    redirect(base_url('kasir'));
                }else if($this->session->userdata('akses') == "tenant"){
                    redirect(base_url('tenant'));
                }else{
                    $this->load->view('header');
                    $this->load->view('dashboard');
                    $this->load->view('footer');
                }
            }
	}
        
}
