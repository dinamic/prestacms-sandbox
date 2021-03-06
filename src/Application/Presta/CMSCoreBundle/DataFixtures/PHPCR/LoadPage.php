<?php
/**
 * This file is part of the Presta Bundle project.
 *
 * @author     Nicolas Bastien <nbastien@prestaconcept.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Application\Presta\CMSCoreBundle\DataFixtures\PHPCR;

use Doctrine\Common\Persistence\ObjectManager;
use PHPCR\Util\NodeHelper;
use Symfony\Component\Yaml\Parser;
use Presta\CMSCoreBundle\DataFixtures\PHPCR\BasePageFixture;

/**
 * @author     Nicolas Bastien <nbastien@prestaconcept.net>
 */
class LoadPage extends BasePageFixture
{
    /**
     * Création des pages de contenu
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $session = $manager->getPhpcrSession();

        //création namespace menu
        NodeHelper::createPath($session, '/website/sandbox/page');
        $root = $manager->find(null, '/website/sandbox/page');

        $yaml = new Parser();
        $datas = $yaml->parse(file_get_contents(__DIR__ . '/../data/page.yml'));
        foreach ($datas['pages'] as $page) {
            $this->createPage($page, $root);
        }

        $this->manager->flush();
    }

    /**
     * {@inherit}
     */
    protected function configureZones(array $page)
    {
        if (!is_null($page['zones'])) {
            return $page;
        }
        if ($page['template'] == 'default') {
            $page['zones'] = array(
                'content' => array(
                    10 => array('name' => 'main', 'type' => 'presta_cms.block.simple')
                ),
            );
            if (count($page['children']) > 1) {
                $page['zones']['content'][20] = array('name' => 'children', 'type' => 'presta_cms.block.page_children');
            }
        }
        if ($page['template'] == 'left-sidebar') {
            $page['zones'] = array(
                'content' => array(
                    10 => array('name' => 'main', 'type' => 'presta_cms.block.simple')
                ),
                'left' => array(
                    10 => array('type' => 'presta_cms.block.simple'),
                    20 => array('type' => 'presta_cms.block.media')
                )
            );
        }

        return $page;
    }

    /**
     * {@inherit}
     */
    protected function configureBlock(array $block)
    {
        $block = parent::configureBlock($block);

        if (count($block['settings'])) {
            return $block;
        }

        switch ($block['type']) {
            case 'presta_cms.block.simple':
                $block['settings'] = array(
                    'en' => array(
                        'title' => 'This is a paragraph block',
                        'content' => 'This is your text. You can edit it in the administration with a WYSIWYG editor.<br/><br/>This content is translatable and has been loaded by PrestaCMS fixtures.',
                    ),
                    'fr' => array(
                        'title' => 'Exemple de bloc paragraphe',
                        'content' => 'Ce bloc est administrable dans le backoffice avec un éditeur WYSIWYG. <br/><br/>Le contenu de ce bloc est traduisible et à été chargé par les fixtures du PrestaCMS.'
                    )
                );
                break;
            case 'presta_cms.block.page_children':
                $block['settings'] = array(
                    'en' => array(
                        'title' => 'This is a page children block',
                        'content' => 'This block displays all page children, each child rendering can be customize by taking advantage of the pages types possibilities.'
                    ),
                    'fr' => array(
                        'title' => 'Exemple de bloc page pallier',
                        'content' => 'Ce bloc vous permet de présenter la liste des pages enfants.<br/><br/>Pour chacun d\'eux le bloc affiche son titre, sa description ainsi qu\'un lien permettant d\'accéder à la page.'
                    )
                );
                break;
            case 'presta_cms.block.media':
                $media = rand(1, 30);
                $block['settings'] = array(
                    'en' => array('media' => (string)$media, 'format' => 'prestacms_page_sidebar'),
                    'fr' => array('media' => (string)$media, 'format' => 'prestacms_page_sidebar')
                );
                break;
            case 'presta_cms.block.media_advanced':
                $block['settings'] = array(
                    'en' => array(
                        'title' => 'Advanced Media Block',
                        'content' => 'This block type allow you to add a media with a tile, a content and an layout option to choose how the block should display.',
                        'media' => 4,
                        'format' => 'prestacms_page_wide'
                    ),
                    'fr' => array(
                        'title' => 'Bloc Média Avancé',
                        'content' => 'Ce bloc vous permet d\'ajouter un média (image, viadeo... suivant votre configuration projet) avec un titre et un contenu. Il est également possible de choisir le style d\'affiche à l\'aide le l\'option "layout"',
                        'media' => 2,
                        'format' => 'prestacms_page_wide'
                    )
                );
                break;

        }
        $block['is_editable'] = true;

        return $block;
    }
}
