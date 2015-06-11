<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\DependencyInjection\ContainerAwareHttpKernel;

class AnnounceController extends Controller{
    private $conn, $session;
    public function __construct(){
        $this -> session = new Session();
        if($this -> session -> get('status') == null)
            $this -> session -> set('status', false);
    }
    /**
    * @Route("/{page}", name="announceHome")
    */
    public function announceHomeAction($page = 0){
        $this -> conn = $this -> get('database_connection');
        $categories = $this -> conn -> fetchAll("select name, guid from category");
        $announces = $this -> conn -> fetchAll(
            sprintf(
                "select a.name, a.guid, c.name, c.guid from announce a inner join category c on a.category_id = c.id where a.status = 0 order by a.id desc limit %d offset 20",
                ($page > 0?$page * 20:20)
            )
        );
        $total = $this -> conn -> fetchAll("select count(id) total from announce where status = 0");
        return $this->render(
            'announce/home.html.twig',
            array(
                "categories" => $categories,
                "announces" => $announces,
                "pages" => $total[0]['total'],
                "user" => $this -> session -> all(),
                "code" => isset($_GET['code'])?$_GET['code']:null
            )
        );
    }
    /**
    * @Route("/busca/{termo}/{$page}", name="announceSearch")
    */
    public function announceSearchAction($termo = null, $page = 0){
        $this -> conn = $this -> get('database_connection');
        $categories = $this -> conn -> fetchAll("select name, guid from category");
        $announces = $this -> conn -> fetchAll(
            sprintf(
                "select a.name, a.guid, c.name, c.guid from announce a inner join category c on a.category_id = c.id inner join announce_data ad on ad.announce_id = a.id where a.status = 0 and (a.name like '%%%s%%' or ad.value like '%%%s%%') order by a.id desc limit %d offset 20",
                ($page > 0?$page * 20:20)
            )
        );
        $total = $this -> conn -> fetchAll(
            sprintf(
                "select count(a.id) total from announce a inner join announce_data ad on a.id = ad.announce_id where a.status = 0 and (a.name like '%%%s%%' or ad.value like '%%%s%%')"
            )
        );
        return $this->render(
            'announce/search.html.twig',
            array(
                "categories" => $categories,
                "announces" => $announces,
                "pages" => $total[0]['total']
            )
        );
    }
    /**
    * @Route("/c/{category}/{page}", name="announceCategories")
    */
    public function announceCategoryAction($category = null, $page = 0){
        $this -> conn = $this -> get('database_connection');
        $categories = $this -> conn -> fetchAll("select name, guid from category");
        $category = $this -> conn -> fetchAll(
            sprintf(
                "select name, guid from category where guid = '%s'",
                $category
            )
        );
        $announces = $this -> conn -> fetchAll(
            sprintf(
                "select a.name, a.guid, c.name, c.guid from announce a inner join category c on a.category_id = c.id where status = 0 and c.guid = '%s' order by a.id desc limit %d offset 20",
                $category,
                ($page > 0?$page * 20:20)
            )
        );
        $total = $this -> conn -> fetchAll(
            sprintf(
                "select a.name, a.guid, c.name, c.guid from announce a inner join category c on a.category_id = c.id where status = 0 and c.guid = '%s'",
                $category
            )
        );
        return $this->render(
            'announce/category.html.twig',
            array(
                "categories" => $categories,
                "category" => $category,
                "announces" => $announces,
                "pages" => $total[0]['total']
            )
        );
    }
    /**
    * @Route("/c/{category}/{guid}", name="announceSingle")
    */
    public function announceSingleAction($category = null, $guid = null){
        $this -> conn = $this -> get('database_connection');
        $categories = $this -> conn -> fetchAll("select name, guid from category");
        $category = $this -> conn -> fetchAll(
            sprintf(
                "select name, guid from category where guid = '%s'",
                $category
            )
        );
        $announces = $this -> conn -> fetchAll(
            sprintf(
                "select a.name, a.guid, c.name, c.guid from announce a inner join category c on a.category_id = c.id where status = 0 and c.guid = '%s' order by a.id desc limit 5",
                $category
            )
        );
        $announce = $this -> conn -> fetchAll(
            sprintf(
                "select a.id, a.name, a.guid, c.name, c.guid from announce a inner join category c on a.category_id = c.id where a.guid = '%s'",
                $guid
            )
        );
        $data = $this -> conn -> fetchAll(
            sprintf(
                "select name, value from announce_data where announce_id = %d",
                $announce[0]['id']
            )
        );
        $comment = $this -> conn -> fetchAll(
            sprintf(
                "select text from comment where user_id = %d",
                $this -> session -> get('id')
            )
        );
        return $this->render(
            'announce/single.html.twig',
            array(
                "categories" => $categories,
                "category" => $category,
                "announce" => $announce,
                "announces" => $announces,
                "data" => $data,
                "comment" => $comment,
                "user" => $this -> session -> get()
            )
        );
    }
    /**
    * @Route("/u/{id}/anuncios", name="announceMy")
    */
    public function announceMyAction($id = null){
        $this -> conn = $this -> get('database_connection');
        $announces = $this -> conn -> fetchAll(
            sprintf(
                "select a.name, a.guid, c.name, c.guid from announce a inner join category c on a.category_id = c.id where a.user_id = %d",
                $id
            )
        );
        return $this->render(
            'announce/my.html.twig',
            array(
                "announces" => $announces,
                "user" => $this -> session -> get()
            )
        );
    }
    /**
    * @Route("/u/{id}/anuncios/{guid}", name="announceView")
    */
    public function announceViewAction($id = null){
        $this -> conn = $this -> get('database_connection');
        $announce = $this -> conn -> fetchAll(
            sprintf(
                "select a.id, a.name, a.guid, c.name, c.guid from announce a inner join category c on a.category_id = c.id where a.guid = '%s'",
                $guid
            )
        );
        $data = $this -> conn -> fetchAll(
            sprintf(
                "select name, value from announce_data where announce_id = %d",
                $announce[0]['id']
            )
        );
        $comment = $this -> conn -> fetchAll(
            sprintf(
                "select text from comment where user_id = %d",
                $this -> session -> get('id')
            )
        );
        return $this->render(
            'announce/view.html.twig',
            array(
                "categories" => $categories,
                "category" => $category,
                "announce" => $announce,
                "announces" => $announces,
                "data" => $data,
                "comment" => $comment,
                "user" => $this -> session -> get()
            )
        );
    }
    /**
    * @Route("/u/{id}/anuncios/novo", name="announceNew")
    */
    public function announceNewAction($id = null){
        $this -> conn = $this -> get('database_connection');
        $categories = $this -> conn -> fetchAll("select name, guid from category");
        return $this->render(
            'announce/single.html.twig',
            array(
                "categories" => $categories,
                "user" => $this -> session -> get()
            )
        );
    }
    /**
    * @Route("/u/{id}/anuncios/{guid}/delete", name="announceDelete")
    */
    public function announceDeleteAction($id = null){
        $this -> conn = $this -> get('database_connection');
        $this -> conn -> fetchAll(
            sprintf(
                "update announce set status = 3 where id = %d",
                $id
            )
        );
        return $this->redirect($this->generateUrl('announceMy'));
    }
}