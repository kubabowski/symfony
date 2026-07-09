<?php

namespace App\Controller\Admin;

use App\Entity\Content;
use App\Repository\ContentRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;

class ContentCrudController extends AbstractCrudController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
        private ContentRepository $contentRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Content::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addFieldset('Basics'),
            IdField::new('id')->onlyOnIndex(),
            TextField::new('title'),
            SlugField::new('slug')->setTargetFieldName('title'),
            TextField::new('type')->setHelp('e.g. hero, about, cta — only used for sections'),
            TextareaField::new('body'),
            IntegerField::new('position'),
            BooleanField::new('isActive', 'Active'),
            AssociationField::new('parent')->autocomplete()->hideOnIndex(),

            FormField::addFieldset('Text fields')->collapsible()->hideOnIndex(),
            TextareaField::new('text1')->hideOnIndex(),
            TextareaField::new('text2')->hideOnIndex(),
            TextareaField::new('text3')->hideOnIndex(),
            TextareaField::new('text4')->hideOnIndex(),
            TextareaField::new('text5')->hideOnIndex(),
            TextareaField::new('text6')->hideOnIndex(),

            FormField::addFieldset('Images')->collapsible()->hideOnIndex(),
            ImageField::new('image1')->setUploadDir('public/uploads/content')->setBasePath('uploads/content')->hideOnIndex(),
            ImageField::new('image2')->setUploadDir('public/uploads/content')->setBasePath('uploads/content')->hideOnIndex(),
            ImageField::new('image3')->setUploadDir('public/uploads/content')->setBasePath('uploads/content')->hideOnIndex(),
            ImageField::new('image4')->setUploadDir('public/uploads/content')->setBasePath('uploads/content')->hideOnIndex(),

            FormField::addFieldset('Buttons')->collapsible()->hideOnIndex(),
            TextField::new('btnText1')->hideOnIndex(),
            TextField::new('btnLink1')->hideOnIndex(),
            TextField::new('btnText2')->hideOnIndex(),
            TextField::new('btnLink2')->hideOnIndex(),
        ];
    }
    // Only show top-level pages by default; show children when ?parentId=X is present
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $parentId = $this->getContext()->getRequest()->query->get('parentId');

        if ($parentId) {
            $qb->andWhere('entity.parent = :parentId')->setParameter('parentId', $parentId);
        } else {
            $qb->andWhere('entity.parent IS NULL');
        }

        return $qb;
    }

    // Preset the parent when creating a new item from inside a filtered ("Sections") view
    public function createEntity(string $entityFqcn)
    {
        $content = new Content();

        $parentId = $this->getContext()->getRequest()->query->get('parentId');
        if ($parentId) {
            $content->setParent($this->contentRepository->find($parentId));
        }

        return $content;
    }



    public function configureActions(Actions $actions): Actions
    {
        $viewSections = Action::new('viewSections', 'Sections', 'fa fa-folder-open')
            ->linkToUrl(fn (Content $content) => $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->set('parentId', $content->getId())
                ->generateUrl())
            ->displayIf(static fn (Content $content) => null === $content->getParent())
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, $viewSections)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->linkToUrl(function () {
                    $parentId = $this->getContext()->getRequest()->query->get('parentId');

                    $url = $this->adminUrlGenerator
                        ->setController(self::class)
                        ->setAction(Action::NEW);

                    if ($parentId) {
                        $url->set('parentId', $parentId);
                    }

                    return $url->generateUrl();
                });
            })
            ;
    }
}