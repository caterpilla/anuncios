<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\DependencyInjection\ContainerAwareHttpKernel;

class UserController extends Controller{
	private $conn, $session;
	public function __construct(){
        $this -> session = new Session();
	}
    /**
     * @Route("/login", name="home")
     */
    public function userLoginAction(Request $request){
    	$pwd = $request -> get('pwd');
    	$email = $request -> get('email');
    	$this -> conn = $this -> get('database_connection');
        $user = $this -> conn -> fetchAssoc(
            sprintf(
                "select email, pwd, id from user where email = '%s' and status = 1",
                $email
            )
        );
        if(count($user) < 0){
            return json_encode(
                array('status' => false)
            );
        }
        if(sha1($pwd) <> $user['pwd']){
            return json_encode(
                array('status' => false)
            );
        }
        $name = $this -> conn -> fetchAssoc(
            sprintf(
                "select value from user_data where user_id = %d and name = 'name'",
                $user['id']
            )
        );
        $this -> session -> set('name', $user -> name);
        $this -> session -> set('id', $user -> id);
        $this -> session -> set('status', true);
        return $this->redirect($this->generateUrl('home'));
    }
    /**
     * @Route("/conta", name="userMyAccount")
     */
    public function userMyAccountAction(Request $request){

    }
    /**
     * @Route("/criar-conta", name="userSignup")
     */
    public function userSignupAction(Request $request){
    	$email = $request -> get('email');
    	$pwd = $request -> get('pwd');
    	$name = $request -> get('name');
        $this -> conn = $this -> get('database_connection');
        $this -> conn -> insert(
            'user',
            array(
                'email' => $email,
                'pwd' => sha1($pwd),
            )
        );
        $id = $this -> conn -> lastInsertId();
        $this -> conn -> insert(
            'user_data',
            array(
                'name' => 'name',
                'value' => $name,
                'user_id' => $id
            )
        );
        $this -> session -> set('name', $user -> name);
        $this -> session -> set('id', $user -> id);
        $this -> session -> set('status', true);
        return $this->redirect($this->generateUrl('home'));
    }
    /**
     * @Route("/esqueci-minha-senha", name="userForget")
     */
    public function userForgetAction(Request $request){

    }
}