<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Exception\InsufficientEntityPermissionException;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ProfileCrudController extends AbstractCrudController
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // the labels used to refer to this entity in titles, buttons, etc.
            ->setEntityLabelInSingular('My Profile')
            ->setEntityPermission('ROLE_ADMIN')
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->disable(Action::NEW, Action::DELETE, Action::DETAIL, Crud::PAGE_INDEX);

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        $form = [
            TextField::new('firstname'),
            TextField::new('lastname'),
            EmailField::new('email'),
            TextField::new('plainPassword', 'Password')->setHelp('enter password only if you need to update it'),
        ];

        return $form;
    }

    public function edit(AdminContext $context)
    {
        $event = new BeforeCrudActionEvent($context);
        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $this->get('event_dispatcher');
        $eventDispatcher->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        if (!$this->isGranted(Permission::EA_EXECUTE_ACTION)) {
            throw new ForbiddenActionException($context);
        }

        if (!$context->getEntity()->isAccessible()) {
            throw new InsufficientEntityPermissionException($context);
        }

        /** @var EntityFactory $entityFactory */
        $entityFactory = $this->get(EntityFactory::class);
        $entityFactory->processFields($context->getEntity(), FieldCollection::new($this->configureFields(Crud::PAGE_EDIT)));
        $entityFactory->processActions($context->getEntity(), $context->getCrud()->getActionsConfig());

        $entityInstance = $context->getEntity()->getInstance();
        $editForm = $this->createEditForm($context->getEntity(), $context->getCrud()->getEditFormOptions(), $context);
        $editForm->handleRequest($context->getRequest());
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $event = new BeforeEntityUpdatedEvent($entityInstance);
            $eventDispatcher->dispatch($event);
            $entityInstance = $event->getEntityInstance();
            /** @var Registry $doctrine */
            $doctrine = $this->get('doctrine');
            /** @var EntityManagerInterface $em */
            $em = $doctrine->getManagerForClass($context->getEntity()->getFqcn());
            $this->updateEntity($em, $entityInstance);

            $eventDispatcher->dispatch(new AfterEntityUpdatedEvent($entityInstance));

            $submitButtonName = $context->getRequest()->request->get('ea')['newForm']['btn'];
            if (Action::SAVE_AND_CONTINUE === $submitButtonName) {
                /** @var CrudUrlGenerator $crudUrlGenerator */
                $crudUrlGenerator = $this->get(CrudUrlGenerator::class);
                $url = $crudUrlGenerator->build()
                    ->setAction(Action::EDIT)
                    ->setEntityId($context->getEntity()->getPrimaryKeyValue())
                    ->generateUrl();

                return $this->redirect($url);
            }

            return $this->redirectToRoute($context->getDashboardRouteName());
        }

        $responseParameters = $this->configureResponseParameters(KeyValueStore::new([
            'pageName' => Crud::PAGE_EDIT,
            'templateName' => 'crud/edit',
            'edit_form' => $editForm,
            'entity' => $context->getEntity(),
        ]));

        $event = new AfterCrudActionEvent($context, $responseParameters);
        $eventDispatcher->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        return $responseParameters;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        /** @var User $user */
        $user = $this->getUser();

        return $queryBuilder->where('entity.id = :id')->setParameter('id', $user->getId());
    }
}
