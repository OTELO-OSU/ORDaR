<?php
namespace search\model;


use Illuminate\Database\Eloquent\Model as model;

class MailValidation extends model{
	public $incrementing  = false;
	protected $table = 'mail_validation';
	protected $primaryKey = 'mail';
	private $mailvalidation;



	
}
