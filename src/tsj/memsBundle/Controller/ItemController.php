<?php

namespace tsj\memsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use tsj\memsBundle\Entity\Item;
use tsj\memsBundle\Form\ItemType;


/**
 * Item controller.
 *
 */
class ItemController extends Controller
{

    /**
     * Lists all Item entities.
     *
     */
    public function indexAction($page = 0)
    {
        $em    = $this->get('doctrine.orm.entity_manager');
        /**
         * Stworzenie obiektu query buildera, do pobrania odpowiedniej listy pozycji
         * Przekazywany do paginacji
         */
        $qb = $em->createQueryBuilder('i')
            ->select('i')
            ->from('tsjmemsBundle:Item', 'i')
            ->orderBy('i.created_at', 'DESC');

        //obiekt paginator - do zbudowania nawigacji
        $paginator = $this->get('tsjmems.pagination');
        //itemy dla aktualnej strony
        $pageItems = $paginator->paginate($qb, $page);

        return $this->render('tsjmemsBundle:Item:index.html.twig', array(
            'pageItems' => $pageItems,
            'paginator' => $paginator
        ));
    }

    /**
     * Lists all Item entities.
     *
     */
    public function favouritesAction($page = 0)
    {
        $userId = $this->getUser()->getId();
        $em    = $this->get('doctrine.orm.entity_manager');
        /**
         * Stworzenie obiektu query buildera, do pobrania odpowiedniej listy pozycji
         * Przekazywany do paginacji
         */
        $qb = $em->createQueryBuilder()
            ->select('i')
            ->from('tsjmemsBundle:Item', 'i')
            ->join('i.usersFavourite', 'u')
            ->where('u.id = ?1')
            ->orderBy('i.created_at', 'DESC')
            ->setParameter(1, $userId);

        //obiekt paginator - do zbudowania nawigacji
        $paginator = $this->get('tsjmems.pagination');
        //itemy dla aktualnej strony
        $pageItems = $paginator->paginate($qb, 2);

        return $this->render('tsjmemsBundle:Item:index.html.twig', array(
            'pageItems' => $pageItems,
            'paginator' => $paginator
        ));
    }

    /**
     * Akcja "Idz do strony" - przekierowuje na strone wybrana w formularzu
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function goToPageAction()
    {
        $page = $this->get('request')->query->get('page');
        if (empty($page)) $page = 0;
        return $this->redirect($this->generateUrl('item_page',['page'=>$page]));
    }

    public function  testAction()
    {
        $em = $this->getDoctrine()->getManager();
         $uId = 1;
        $iId = 5;
        $user = $this->getUser();
        $favouriteItem = $em->getRepository('tsjmemsBundle:Item')->find($iId);
        $user->addFavourite($favouriteItem);
        $em->persist($user);
        $em->flush();

        return new \Symfony\Component\HttpFoundation\Response();
    }
    /**
     * Creates a new Item entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Item();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('tsj_item_show', array('id' => $entity->getId())));
        }

        return $this->render('tsjmemsBundle:Item:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Item entity.
    *
    * @param Item $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Item $entity)
    {
        $form = $this->createForm(new ItemType(), $entity, array(
            'action' => $this->generateUrl('item_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Item entity.
     *
     */
    public function newAction()
    {
        $entity = new Item();
        $form   = $this->createCreateForm($entity);

        return $this->render('tsjmemsBundle:Item:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Item entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('tsjmemsBundle:Item')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Item entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('tsjmemsBundle:Item:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing Item entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('tsjmemsBundle:Item')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Item entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('tsjmemsBundle:Item:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Item entity.
    *
    * @param Item $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Item $entity)
    {
        $form = $this->createForm(new ItemType(), $entity, array(
            'action' => $this->generateUrl('tsj_item_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Item entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('tsjmemsBundle:Item')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Item entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('tsj_item_edit', array('id' => $id)));
        }

        return $this->render('tsjmemsBundle:Item:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Item entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('tsjmemsBundle:Item')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Item entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('tsj_item'));
    }

    /**
     * Creates a form to delete a Item entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('tsj_item_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
