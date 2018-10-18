<?php if (defined('BASEPATH') or exit('Not direct access allowed'));

class Auth_model extends CI_Model
{
	const TABLE = 'user';
	public function login($unique, $pwd)
	{
		return $this->db->select('user_ID')
						->from(self::TABLE)
						->where('user_EMAIL', $unique)
						->where('user_PASS', $pwd)
						->get()
                        ->row();
	}
}