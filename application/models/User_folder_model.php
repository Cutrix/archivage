<?php if (defined('BASEPATH') or exit('No direct access allowed'));

class User_folder_Model extends MY_Model
{
    protected $table = 'User_folder_model';

    function getFolderForUser($idUser) {
        return $this->db->select('dossier_ID, dossier_NOM')
                        ->from('dossier')
                        ->join('user_folder', 'dossier.dossier_ID = user_folder.idFolder')
                        ->get()
                        ->result();
    }
}