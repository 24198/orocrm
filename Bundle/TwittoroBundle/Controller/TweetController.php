<?php

namespace Tfone\Bundle\TwittoroBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Tfone\Bundle\TwittoroBundle\Entity\Tweet;

/**
 * @Route("/tweet")
 */
class TweetController extends Controller
{
    /**
     * @Route(
     *      ".{_format}",
     *      name="tfone_twittoro_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @Acl(
     *      id="tfone_twittoro_index",
     *      type="entity",
     *      class="TfoneTwittoroBundle:Tweet",
     *      permission="VIEW"
     * )
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/create", name="tfone_twittoro_create")
     * @Template("TfoneTwittoroBundle:Tweet:update.html.twig")
     * @Acl(
     *      id="tfone_twittoro_create",
     *      type="entity",
     *      class="TfoneTwittoroBundle:Tweet",
     *      permission="CREATE"
     * )
     */
    public function createAction()
    {
        $entity = new Tweet();

        return $this->update($entity);
    }

    /**
     * @Route("/view/{id}", name="tfone_twittoro_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="tfone_twittoro_view",
     *      type="entity",
     *      class="TfoneTwittoroBundle:Tweet",
     *      permission="VIEW"
     * )
     */
    public function viewAction(Tweet $entity)
    {
        return array('entity' => $entity);
    }

    /**
     * @Route("/update/{id}", name="tfone_twittoro_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="tfone_twittoro_update",
     *      type="entity",
     *      class="TfoneTwittoroBundle:Tweet",
     *      permission="EDIT"
     * )
     */
    public function updateAction(Tweet $entity)
    {
        return $this->update($entity);
    }

    /**
     * @param Tweet $entity
     * @return array
     */
    protected function update(Tweet $entity)
    {
        $request = $this->getRequest();
        $form = $this->createForm($this->get('tfone_twittoro.form.type.tweet'), $entity);

        if ('POST' == $request->getMethod()) {
            $form->submit($request);
            if ($form->isValid()) {
                $this->getDoctrine()->getManager()->persist($entity);
                $this->getDoctrine()->getManager()->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('tfone.twittoro.saved_message')
                );

                return $this->get('oro_ui.router')->actionRedirect(
                    array(
                        'route' => 'tfone_twittoro_update',
                        'parameters' => array('id' => $entity->getId()),
                    ),
                    array(
                        'route' => 'tfone_twittoro_view',
                        'parameters' => array('id' => $entity->getId()),
                    )
                );
            }
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }    
}