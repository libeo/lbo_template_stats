<?php

declare(strict_types=1);

namespace Libeo\LboTemplateStats\Controller;


use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * This file is part of the "Template Stats BE" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022
 */

/**
 * BackendLayoutController
 */
class BackendLayoutController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    private $cache;

    /**
     * action list
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function listAction(): \Psr\Http\Message\ResponseInterface
    {
        $this->cache = GeneralUtility::makeInstance(CacheManager::class)->getCache($this->settings['cacheName']);

        if (!$pagesWithLayouts = $this->cache->get('pagesWithLayouts')) {
            $pagesWithLayouts = $this->fetchALlPagesWithLayouts();
            $this->cache->set('pagesWithLayouts', $pagesWithLayouts);
        }

        $backendLayouts = $this->fetchAllLayouts();

        if (isset($_POST['tx_lbotemplatestats_web_lbotemplatestatstemplatestats']['layoutToShow'])) {
            $layoutTitle = $_POST['tx_lbotemplatestats_web_lbotemplatestatstemplatestats']['layoutToShow'];
            $layoutTitle = $this->formatLayoutTitle($layoutTitle);
        } else {
            $layoutTitle = $this->formatLayoutTitle(array_key_first($backendLayouts));
        }

        if (!$layoutPages = $this->cache->get($layoutTitle)) {
            $layoutPages = [];
            $this->fetchPagesWithLayout($pagesWithLayouts, $layoutPages, $layoutTitle);
            $this->cache->set($layoutTitle, $layoutPages);
        }

        $this->view->assign('layoutTitle', $layoutTitle);
        $this->view->assign('layoutPages', $layoutPages);
        $this->view->assign('backendLayouts', $backendLayouts);

        return $this->htmlResponse();
    }

    /**
     * @return array de tous les backend_layout présents dans le site, qu'ils soient utilisés ou non
     */
    protected function fetchAllLayouts(): array
    {
        $rootTs = BackendUtility::getPagesTSconfig(1);
        return $rootTs['mod.']['web_layout.']['BackendLayouts.'];
    }

    /**
     * @param array $pages Array des pages à travers lesquelles chercher
     * @param array $resultArray Array passé en référence, pour pouvoir appeler la fonction par recursion
     * @param string $layoutTitle Titre du backend layout à chercher
     * @param bool $isRecursive Modifie le comportement de la fonction si l'appel est récursif
     * @return void
     */
    protected function fetchPagesWithLayout(array $pages, array &$resultArray, string $layoutTitle, bool $isRecursive = false): void
    {
        $nextLevelArray = [];

        foreach ($pages as $page) {
            if (!in_array($page, $resultArray)) {
                if ($page['backend_layout'] == $layoutTitle) {
                    $resultArray[] = $page;
                }
            }
            if ($page['backend_layout_next_level'] == $layoutTitle || ($isRecursive && $page['backend_layout_next_level'] == '')) {
                $nextLevelPages = $this->fetchNextLevel($page['uid']);
                array_push($resultArray, ...$nextLevelPages);
                array_push($nextLevelArray, ...$nextLevelPages);
            }
        }

        if (count($nextLevelArray) > 0) {
            $this->fetchPagesWithLayout($nextLevelArray, $resultArray, $layoutTitle, true);
        }
    }

    /**
     * @param int $parentUid Uid de la page parente
     * @return array des pages enfantes ayant un champ backend_layout vide, et donc qui héritent de celui du parent
     */
    protected function fetchNextLevel(int $parentUid): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $statement = $queryBuilder
            ->select('backend_layout', 'backend_layout_next_level', 'uid', 'title')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($parentUid))
            )
            ->andWhere(
                $queryBuilder->expr()->eq('backend_layout', $queryBuilder->createNamedParameter(''))
            )
            ->execute();
        return $statement->fetchAllAssociative();
    }

    /**
     * @return array de toutes les pages du site ayant un backend_layout et/ou un backend_layout_next_level
     */
    protected function fetchAllPagesWithLayouts(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $statement = $queryBuilder
            ->select('backend_layout', 'backend_layout_next_level', 'uid', 'title')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->neq('backend_layout', $queryBuilder->createNamedParameter(''))
            )
            ->orWhere(
                $queryBuilder->expr()->neq('backend_layout_next_level', $queryBuilder->createNamedParameter(''))
            )
            ->execute();
        return $statement->fetchAllAssociative();
    }

    /**
     * @return string titre du layout comme généré par typo3
     */
    protected function formatLayoutTitle(string $layoutTitle): string
    {
        return 'pagets__' . rtrim($layoutTitle, '.');
    }
}
