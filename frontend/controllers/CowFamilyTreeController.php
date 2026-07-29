<?php

namespace frontend\controllers;

use common\models\CowFamilyTree;
use common\models\Livestock;
use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;

class CowFamilyTreeController extends ActiveController
{
    public $modelClass = 'app\models\CowFamilyTree';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

    public function actionCreate()
    {
        $model = new CowFamilyTree();
        $model->load(Yii::$app->request->post(), '');

        if ($model->validate() && $model->save()) {
            return $model;
        }

        return ['errors' => $model->errors];
    }

    public function actionUpdate(int $id)
    {
        $model = CowFamilyTree::findOne($id);
        if (!$model) throw new NotFoundHttpException("Tree ID $id tidak ditemukan.");

        $model->load(Yii::$app->request->post(), '');

        if ($model->validate() && $model->save()) {
            return $model;
        }

        return ['errors' => $model->errors];
    }

    public function actionDelete(int $id)
    {
        $model = CowFamilyTree::findOne($id);
        if (!$model) throw new NotFoundHttpException("Tree ID $id tidak ditemukan.");
        $model->delete();
        return ['message' => 'Tree berhasil dihapus.'];
    }

    public function actionGetCowFamilyTree(int $id)
    {
        $cow = Livestock::findOne($id);
        if (!$cow) {
            throw new NotFoundHttpException("Sapi tidak ditemukan.");
        }

        // Ambil data tree berdasarkan main_cow_id
        $treeEntry = CowFamilyTree::findOne(['main_cow_id' => $id]);
        if (!$treeEntry) {
            throw new NotFoundHttpException("Silsilah untuk sapi ini belum dibuat.");
        }

        $tree = $this->buildFamilyTree($id);

        return [
            'id' => $cow->id,
            'name' => $cow->name,
            'tree' => $tree
        ];
    }

    private function buildFamilyTree(int $mainCowId)
    {
        $treeEntry = CowFamilyTree::findOne(['main_cow_id' => $mainCowId]);
        $tree = [];

        $tree['main'] = $this->getBasicCowInfo($mainCowId);

        $tree['partners'] = [];
        foreach ($treeEntry->partners as $partnerId) {
            $partner = $this->getBasicCowInfo($partnerId);
            $tree['partners'][] = $partner;
        }

        $tree['children'] = [];
        foreach (CowFamilyTree::getChildrenIds($mainCowId) as $childId) {
            $tree['children'][] = $this->getBasicCowInfo($childId);
        }

        // Ambil orang tua
        $parentsEntry = CowFamilyTree::findOne(['main_cow_id' => $mainCowId]);
        if ($parentsEntry && ($parentsEntry->father_id || $parentsEntry->mother_id)) {
            $tree['parents'] = [
                'father' => $this->getBasicCowInfo($parentsEntry->father_id),
                'mother' => $this->getBasicCowInfo($parentsEntry->mother_id)
            ];

            // Cari saudara kandung dari orang tua
            $tree['parent_siblings'] = [];
            foreach (['father_id', 'mother_id'] as $parentField) {
                $parentId = $parentsEntry->$parentField;
                $tree['parent_siblings'][$parentField] = $this->getSiblings($parentId);
            }
        }

        // Saudara kandung dan tiri
        $tree['siblings'] = $this->getSiblings($mainCowId);

        // Sepupu
        $tree['cousins'] = $this->getCousins($mainCowId);

        // Keponakan
        $tree['nephews'] = $this->getNephews($mainCowId);

        // Semua keturunan (anak, cucu, dst.)
        $tree['descendants'] = CowFamilyTree::getDescendantsGrouped($mainCowId);

        return $tree;
    }

    private function getBasicCowInfo(int $cowId)
    {
        $cow = Livestock::findOne($cowId);
        return $cow ? [
            'id' => $cow->id,
            'name' => $cow->name,
        ] : null;
    }

    private function getSiblings(int $cowId)
    {
        // Pastikan cowId valid sebelum mencari siblings
        if (!$cowId) {
            return [];
        }
        
        $ids = CowFamilyTree::getSiblingIds($cowId);
        $siblings = [];
        foreach ($ids as $id) {
            // Filter untuk memastikan tidak menambahkan diri sendiri
            if ($id != $cowId) {
                $siblings[] = $this->getBasicCowInfo($id);
            }
        }
        return $siblings;
    }

    private function getCousins(int $cowId)
    {
        $cousins = [];
        $entry = CowFamilyTree::findOne(['main_cow_id' => $cowId]);
        if (!$entry) return [];

        $parentSiblings = array_merge(
            $this->getSiblings($entry->father_id),
            $this->getSiblings($entry->mother_id)
        );

        foreach ($parentSiblings as $uncleAunt) {
            $uncleEntry = CowFamilyTree::findOne(['main_cow_id' => $uncleAunt['id']]);
            if ($uncleEntry) {
                foreach (CowFamilyTree::getChildrenIds($uncleAunt['id']) as $cousinId) {
                    $cousins[] = $this->getBasicCowInfo($cousinId);
                }
            }
        }

        return $cousins;
    }

    private function getNephews(int $cowId)
    {
        $nephews = [];
        $siblings = $this->getSiblings($cowId);

        foreach ($siblings as $sibling) {
            $entry = CowFamilyTree::findOne(['main_cow_id' => $sibling['id']]);
            if ($entry) {
                foreach (CowFamilyTree::getChildrenIds($sibling['id']) as $nephewId) {
                    $nephews[] = $this->getBasicCowInfo($nephewId);
                }
            }
        }

        return $nephews;
    }
}
