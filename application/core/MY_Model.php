<?php if (defined('BASEPATH') or exit('No direct access allowed'));

class MY_Model extends CI_Model
{
	/**
	 * Permet de creer une entree dans la base de donee
	 * @param array $esc_opt les donnees echappes
	 * @param array $esc_n_opt les donnees non echappees 	 
     * @return bool
	 * @author houessinon landry Ayode
    */
		
	public function create($esc_opt = array(), $esc_n_opt = array())
	{
		if (empty($esc_opt) AND empty($esc_n_opt))		
		{
			return false;
		}

		return (bool) $this->db->set($esc_opt)
							   ->set($esc_n_opt, null, false)	
							   ->insert($this->table);
	}

	
	/**
	 * Permet de lire une entree en bdd
	 * @author houessinon landry Ayode
	 * @param  $select Selectionner une entree specifique en base de donnee
	 * @param  $nb Le nombre de requete a selectionnee
	 * @param  $debut A selectiooner
	 */
	
	public function read($select='*', $where=array(), $nb = null, $debut = null)
	{
		return $this->db->select($select)
						->from($this->table)
						->where($where)
						->limit($nb, $debut)
						->get()
						->result();
	}


    /**
     * 
     * 
     *
     */
	public function update($where, $esc_opt = array(), $esc_n_opt = array())
	{
		if (empty($esc_opt) AND empty($esc_n_opt))
		{
			return false;
		}

		return (bool) $this->db->set($esc_opt)
						       ->set($esc_n_opt, null, false)
						       ->where($where)
						       ->update($this->table);
	}


	public function delete($where)
	{
		return $this->db->where($where)
						->delete($this->table);
	}


	public function count($champ = array(), $valeur = null)
	{
		return (int) $this->db->where($champ, $valeur)
							  ->from($this->table)
							  ->count_all_results();
	}
}