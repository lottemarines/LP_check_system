<?php

// http://lpc.rentracks.work/Csv/show


defined('BASEPATH') OR exit('No direct script access allowed');

class Csv extends CI_Controller {

    public function show(){
        require_once(APPPATH.'config/data.php');
	    $this->load->helper('url');
	    $this->load->helper('form');
	    $this->load->helper('html');
	    $this->load->helper('file');
	    $this->load->model('model');
    	$this->load->library('form_validation');
    	$this->form_validation->set_rules('genre_id', 'ジャンル', 'required');
    	$this->form_validation->set_message('required', '%sが入力されていません。');
    	
		if ($this->form_validation->run() == FALSE)
        {
        	$this->load->view('insertcsv');

        }else{
        	//データの入力
        	$data['csv'] = nl2br($this->input->post('csvfile'));
        	$data['genre_id'] = $this->input->post('genre_id');
        	if(empty($this->input->post('wildcard'))){
        		$data["wildcard"] = "0";
        	}else{
        		$data["wildcard"] = $this->input->post('wildcard');
        	};
        	$data['genre_sub_id'] = $this->input->post('genre_sub_id');

        	$count_n = substr_count($data['csv'],"\n");

        	
        	$record_array = explode("\n", $data['csv']);
        	//DELETE FROM `ng_words` WHERE `ng_words`.`no` BETWEEN 131 and 100000
        	//$data['csv']にng_wordとrevisionデータを改行ごとに切り分けて配列化させたい
        	foreach($record_array as $record_ng){
        		//最初はスキップする
        		if($record_ng === reset($record_array)){
        			continue;
        		}

        		//NG登録
        		$ng_array = explode(",", $record_ng);
        		if(!empty($ng_array[0])){

        			$ng_array[1] = str_replace("<br />", '', $ng_array[1]);
        			$ng_array[1] = trim($ng_array[1]);
        			// var_dump($ng_array[1]);
        			// $ng_array[1] = str_replace(PHP_EOL, '', $ng_array[1]);
	        		$ng_word = $ng_array[0];
	        		$revision = $ng_array[1];
	        		$this->model->insert_data($data['genre_id'], $data['genre_sub_id'], $ng_word, $revision, $data["wildcard"]);
        	}
        	}
        	

			$this->load->view('csv_thanks', $data);
        }
    }

	
}


?>