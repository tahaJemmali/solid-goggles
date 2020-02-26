<?php

namespace EventBundle\Controller;

use ECommerceBundle\Entity\Cart;
use EventBundle\Entity\Evenement;

use EventBundle\Entity\Score;
use EventBundle\Form\EditEventType;
use EventBundle\Form\EvenementType;
use LoginBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;


/**
 * Evenement controller.
 *
 */
class EvenementController extends Controller
{
    /**
     * Lists all evenement entities.
     *
     */
    public function indexAction()
    {
        $cart='';
        if ($this->getUser())
            $cart = $this->getDoctrine()->getRepository(Cart::class)->Cart_user_id($this->getUser()->getId());

        $em = $this->getDoctrine()->getManager();
        $bests = $this->getDoctrine()->getRepository(User::class)->findTop();
        $evenements = $em->getRepository('EventBundle:Evenement')->findAll();
        $particpant = $em->getRepository('EventBundle:Evenement')->particpant();
        return $this->render('@Event/evenement/index.html.twig',compact("evenements","bests","particpant","cart"));
    }

    /**
     * Finds and displays a evenement entity.
     * @param Evenement $evenement
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Evenement $evenement)
    {
        return $this->render('@Event/evenement/show.html.twig', compact("evenement"));
    }


    public function addAction(Request $request)
    {   $event=new Evenement();
        $form=$this->createForm(EvenementType::class,$event);
        $form=$form->add('ajouter',SubmitType::class,array(
            'attr'=> array('class'=>'button','onclick'=> 'getCurrent()')) );
        $form=$form->handleRequest($request);
        if ($form->isSubmitted())
        {
            $date=$event->getDateDebut();
            $date = \DateTime::createFromFormat('Ymd', $date);
            $event->setDateDebut($date);
          $event->setDateCreation(new \DateTime('now'));
            foreach ($_FILES['eventbundle_evenement']['name'] as $name => $value)
            {
                $my_file_name = explode(".", $_FILES['eventbundle_evenement']['name'][$name]);
                $NewImageName = md5(rand()) . '.' . $my_file_name[1];
                $SourcePath = $_FILES['eventbundle_evenement']['tmp_name'][$name];
                $TargetPath = "uploads/".$NewImageName;
                move_uploaded_file($SourcePath, $TargetPath);
            }
            $event->setImage($TargetPath);
            $this->addScore($event->getScore());
            $em=$this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();
            return $this->redirectToRoute("list_evenement_back");
        }
        $form=$form->createView();
        return $this->render('@Event/evenement/add.html.twig',compact("form"));
    }

    public function showBackAction()
    {

        $curl=curl_init('http://api.weatherstack.com/current?access_key=dca5b895825ba930155f756757dfdddc&query=Tunis');
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        //curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        $data=curl_exec($curl);

        if (!$data)
        {
            dump(curl_error($curl));
        }
        else
        {
            $data= json_decode($data,true);
            $temperature=$data['current']['temperature'];
            $weather_icons=$data['current']['weather_icons'];
            $weather_descriptions=$data['current']['weather_descriptions'];
            $country=$data['location']['country'];
        }
        curl_close($curl);
        $weather['temperature']=$temperature;
        $weather['icon']=$weather_icons[0];
        $weather['description']=$weather_descriptions[0];
        $weather['country']=$country;
        $em = $this->getDoctrine()->getManager();
        $evenements = $em->getRepository('EventBundle:Evenement')->findAll();
        $users = $em->getRepository('LoginBundle:User')->findAll();
        $particpant = $em->getRepository('EventBundle:Evenement')->particpant();
        return $this->render('@Event/evenement/back.html.twig',compact("evenements","particpant","users","weather"));
    }

    public function deleteAction($id)
    {
        $em=$this->getDoctrine()->getManager();
        $event=$this->getDoctrine()->getRepository(Evenement::class)->find($id);
        $filesystem = new Filesystem();
        $filesystem->remove($event->getImage());
        $em->remove($event->getScore());
        $em->remove($event);
        $em->flush();
        return $this->redirectToRoute("list_evenement_back");
    }
    public function editAction(Request $request,$id){
        $evenement = $this->getDoctrine()->getRepository(Evenement::class)->find($id);
        $evenement->setImage(null);
        $form= $this->createForm(EditEventType::class,$evenement);
        $form=$form->handleRequest($request);

        if ($form->isSubmitted())
        {
            foreach ($_FILES['eventbundle_evenement']['name'] as $name => $value)
            {
                $my_file_name = explode(".", $_FILES['eventbundle_evenement']['name'][$name]);
                $NewImageName = md5(rand()) . '.' . $my_file_name[1];
                $SourcePath = $_FILES['eventbundle_evenement']['tmp_name'][$name];
                $TargetPath = "uploads/".$NewImageName;
                move_uploaded_file($SourcePath, $TargetPath);
            }
            $evenement->setImage($TargetPath);
            $em= $this->getDoctrine()->getManager();
            $this->addScore($evenement->getScore());
            $em->persist($evenement);

            $em ->flush();
            return $this->redirectToRoute("list_evenement_back");
        }
        $form=$form->createView();
        return $this->render('@Event/evenement/edit.html.twig',compact("form"));
    }

    public function AddScore(Score $score)
    {
        $em= $this->getDoctrine()->getManager();
        $em->persist($score);
        $em ->flush();
    }

    public function inviterAction(Request $request,$id)
    {
        $users = $this->getDoctrine()->getRepository('LoginBundle:User')->inviter($id);
        if (empty($users))
        {
            $users = $this->getDoctrine()->getRepository('LoginBundle:User')->findAll();
            foreach ($users as $key  ){
                $array[$key->getUsername()]=$key;
            }
        }
        else{
            foreach ($users as $key  ){
                $key = $this->getDoctrine()->getRepository('LoginBundle:User')->find($key['id']);
                $array[$key->getUsername()]=$key;
            }
        }
        $evenement = $this->getDoctrine()->getRepository(Evenement::class)->find($id);
        $form=$this->createFormBuilder()->add('users',ChoiceType::class, array('multiple'=>true,'choices'=> $array ))->add('inviter',SubmitType::class )->getForm();
        $form=$form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($evenement);

            $x = $form->getData();
            foreach ($x["users"] as $user)
            {
                $evenement->addUsers($user);
                }
            $em ->flush();

            return $this->redirectToRoute("list_evenement_back");
        }
        $form=$form->createView();
        return $this->render('@Event/evenement/inviter.html.twig',compact("form"));
    }
    public function particperAction($id_event,$id_user)
    {
        $evenement = $this->getDoctrine()->getRepository(Evenement::class)->find($id_event);
        $user = $this->getDoctrine()->getRepository('LoginBundle:User')->find($id_user);
        $em= $this->getDoctrine()->getManager();
        $evenement->addUsers($user);
        $em->persist($evenement);
        $em ->flush();
        $this->addFlash('success', 'félicitation,Vous participer à un nouveau évenement !Voir votre profile pour plus d\'information');
        return $this->redirectToRoute("list_evenement_front");
    }

    public function annulerAction($id_event,$id_user)
    {
        $this->getDoctrine()->getRepository(Evenement::class)->annuler($id_event,$id_user);
        $this->addFlash('success', 'Vous ne participez plus à cet evenement !');
        return $this->redirectToRoute("list_evenement_front");
    }
    public function scoreAction(Request $request,$id)
    {
        $array=[];
        $users = $this->getDoctrine()->getRepository('LoginBundle:User')->membre($id);
        foreach ($users as $key  ){
            $key = $this->getDoctrine()->getRepository('LoginBundle:User')->find($key['id']);
            $array[$key->getUsername()]=$key;
        }
        $event = $this->getDoctrine()->getRepository(Evenement::class)->find($id);
        $form=$this->createFormBuilder()->add('first',ChoiceType::class, array('choices'=> $array ))
            ->add('secound',ChoiceType::class, array('choices'=> $array ))
            ->add('third',ChoiceType::class, array('choices'=> $array ))
            ->add('attribuer',SubmitType::class )->getForm();
        $form=$form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $x = $form->getData();

            $x["first"]->setScore($x["first"]->getScore()+$event->getScore()->getFirst());
            $x["secound"]->setScore($x["secound"]->getScore()+$event->getScore()->getSecound());
            $x["third"]->setScore($x["third"]->getScore()+$event->getScore()->getThird());

            $em->persist($x["first"]);
            $em->persist($x["secound"]);
            $em->persist($x["third"]);
            $this->addFlash('success', 'L\'evenement '.$event->getTitre().' est terminer ! Féliciter '.$x["first"]->getUsername().' le premier ,'.$x["secound"]->getUsername().' le deuxième et'.$x["third"]->getUsername().'  le troisième ');
            $em->remove($event);
            $em ->flush();

            return $this->redirectToRoute("list_evenement_back");
        }
        $form=$form->createView();
        return $this->render('@Event/evenement/inviter.html.twig',compact("form"));
    }
}
