<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\DeleteCustomerType;
use App\Form\ProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class AdminController extends AbstractController
{
    #[Route('/Admin/manage-customer', name: 'setting.customer')]
    public function index(UserRepository $userRepository): Response
    {
        $users=$userRepository->findAll();
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }
    #[Route('/Admin/addCustomer', name: 'setting.addCustomer')]
    public function addCustomer(Request $request, EntityManagerInterface $em, UserRepository $repository){
        $form=$this->createForm(ProfileType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em->persist($repository);
            $em->flush();
            $this->addFlash('success','User added with success !');
            return $this->redirectToRoute('setting.addCustomer');
        }
        return $this->render('user/index.html.twig',[
            'formAddcustomer'=>$form
        ]);
    }
    #[Route('/Admin/deleteCustomer-{id}', name: 'setting.deleteCustomer', requirements:['id'=>Requirement::DIGITS])]
    public function deleteCustomer ($id, User $user,EntityManagerInterface $em, Request $request){
        $formDelete=$this->createForm(DeleteCustomerType::class);
        $formDelete->handleRequest($request);
        if ($formDelete->isSubmitted() && $formDelete->isValid()){
            $em->remove($user);
            $em->flush();
            return $this->redirectToRoute('accueil');
        }
        return $this->render('user/index.html.twig',[
            'formDelete'=>$formDelete
        ]);
    }
    #[Route('/Admin/editCusomer-{id}', name: 'setting.editCustomer', requirements:['id'=>Requirement::DIGITS])]
    public function editCustomer (User $user, Request $request, $id, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            /** @var UploadedFile $file */
            $file=$form->get('thumbnailFile')->getData();
            if (!$file==null){
                $filename= $user->getId().'.'.$user->getEmail().$file->getClientOriginalExtension();
                $file->move($this->getParameter('kernel.project_dir').'/public/assets/images/users',$filename);
                $user->setThumbnail($filename);
            }
            $em->flush();
            $this->addFlash('success','Customer edit with success');
            return $this->redirectToRoute('setting.editCustomer',['id'=>$id]);
        }
        return $this->render('user/index.html.twig', [
            'formEditCustomer' => $form,
            'customer'=>$user,
        ]);
    }
}
