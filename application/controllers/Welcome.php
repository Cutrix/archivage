<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		//Vrification de la session

		$this->load->library('twig');
		$this->load->library(array('form_validation'));

		//helper
        $this->load->helper(array('String', 'File', 'Auth'));

		//Perso des messages
		$this->form_validation->set_message('required', 'le champ %s est requis');
		$this->form_validation->set_message('valid_email', 'l\'adresse email doit etre une addresse email valide');

		$this->load->database();

		$this->load->model('Doc_model', 'docModel');
		$this->load->model('Folder_model', 'folderModel');
		$this->load->model('User_folder_model', 'userFolderModel');
		$this->load->model('User_model', 'userModel');
		$this->load->model('Group_model', 'groupModel');
		$this->load->model('Share_model', 'shareModel');
	}

    public function index()
    {
        $this->twig->display('acceuil');
	}

	public function login()
	{
		$this->form_validation->set_rules('unique', 'mail', 'required|valid_email');
		$this->form_validation->set_rules('pwd', 'mot de passe', 'required');
		$this->form_validation->set_error_delimiters('', '');

		//load model
		$this->load->model('Auth/Auth_model', 'auth');

		if ($this->form_validation->run() === TRUE)
		{
			if ($this->auth->login($this->input->post('unique', true), $this->input->post('pwd', true)) != null)
			{
                $user_id = $this->auth->login($this->input->post('unique', true), $this->input->post('pwd', true))->user_ID;
                $this->session->set_userdata('id_user', $user_id);
			    $this->session->set_userdata('mail', $this->input->post('unique', true));
				redirect('Welcome');
			} else {
				$errors = array('error' => 'Mot de passe et email incorrect! Veuillez ressayer svp');
			}
					
		} else {			
			//$errors = validation_errors();			
			$errors = $this->form_validation->error_array();
		}
		
		if ($errors != null) 
			$this->twig->display('login', array('errors' => $errors)); 
		else
			$this->twig->display('login'); 
			
	}

	public function addDoc()
	{
	    if (userDisconnect($this->session->id_user)) redirect('Welcome/login');
	    $this->form_validation->set_rules('nomDoc', 'nom', 'required');
	    $this->form_validation->set_rules('nomDossier', 'dossier', 'required');

	    $code_doc = random_string('alpha');

	    if ($this->form_validation->run() === TRUE)
        {
            $idDossier = $this->input->post('nomDossier', true);
            $data['document_NOM'] = $this->input->post('nomDoc', true);
            $data['user_AUTEUR_ID'] = $this->session->id_user;
            $data['dossier__CODE'] = $idDossier;
            $data['document_CODE_SAUVEGARDE']= $code_doc;
            $data_n_escape['document_DATE_AJOUT'] = 'NOW()';

            //Configuration pour l'upload

            //il faut creer le dossier si il n'existe pas
            $path = './docs/'.$this->session->id_user.'/'.$idDossier;
            if (!file_exists($path))
            {
                mkdir($path, 0777);
            } else {
                $config['upload_path'] = $path;
                $config['allowed_types'] = 'png|jpg|gif|docx|doc|xls|xlsx|odt|jpeg';
                $config['max_size'] = 512000;
                $config['remove_spaces'] = true;
                $config['file_name'] = $this->input->post('nomDoc', true);

                $this->load->library('upload', $config);
                $this->upload->initialize($config);

                if ($this->upload->do_upload('doc'))
                {
                    $path_upload = $path.'/'.$this->upload->data('orig_name');
                    $data['path_doc'] = $path_upload;
                    $data['tailleDoc'] = $this->upload->data('file_size');
                    var_dump($this->docModel->create($data, $data_n_escape)); die;
                    $this->session->set_flashdata('error', false);
                } else {
                    $this->session->set_flashdata('error', true);
                }
            }
        }
		$this->twig->display('creer_doc', array('docs' => $this->docModel->read(), 'folders' => $this->userFolderModel->getFolderForUser($this->session->id_user),
            'users' => $this->userModel->read(), 'groups' => $this->groupModel->read()));
	}


    public function addFolder()
    {
        if (userDisconnect($this->session->id_user)) redirect('Welcome/login');
        $success = false;
        $this->form_validation->set_rules('nomDossier', 'nom dossier', 'required|max_length[10]');
        if ($this->form_validation->run() === TRUE){
            $code_folder = random_string('alpha');
            $data['dossier_CODE'] = $code_folder;
            $data['dossier_NOM'] = $this->input->post('nomDossier', true);
            $data_n_escape['dossier_DATE_CREATION'] = 'NOW()';

            if ($this->folderModel->create($data, $data_n_escape)) {
                $dataf['idUsr'] = $this->session->id_user;
                $dataf['idFolder'] = $this->db->insert_id();
                $dataf_n_escape['date_creation'] = 'NOW()';

                $this->userFolderModel->create($dataf, $dataf_n_escape);
                $success = true;
                redirect('Welcome/addFolder', 'refresh');
            } else {
                $success = false;
            }
        }
        $this->twig->display('creer_dossier', array('folders' => $this->folderModel->read(), 'state' => $success));
	}

    public function share()
    {
        $data['user_SENDER_ID'] = $this->session->id_user;
        $data['user_RECEPTION'] = (int) $this->input->post('user');
        $data_n_escape['DATE_PARTAGE'] = 'NOW()';
        $data['document_document_ID'] = (int) $this->input->post('idDoc');
        $data['document_CODE_SAUVEGARDE'] = random_string('alpha');

        if ($this->shareModel->create($data, $data_n_escape)) {
            $this->session->set_flashdata('share', true);
        } else {
            $this->session->set_flashdata('share', false);
        }
        redirect('Welcome/addDoc', 'refresh');
	}
}
