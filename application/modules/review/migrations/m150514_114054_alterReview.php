<?php

use app\backend\models\BackendMenu;
use app\modules\review\models\RatingItem;
use app\modules\review\models\RatingValues;
use app\modules\review\models\Review;
use yii\db\Migration;

class m150514_114054_alterReview extends Migration
{
    public function up()
    {
        $this->addColumn(Review::tableName(), 'submission_id', 'INT UNSIGNED NOT NULL');
        $form = new \app\models\Form;
        $form->name = 'Review';
        $form->save(true, ['name']);
        $formId = $this->db->lastInsertID;
        $reviews = Review::find()->all();
        foreach ($reviews as $review) {
            $submission = new \app\models\Submission;
            $submission->form_id = $formId;
            $submission->processed_by_user_id = $review->author_user_id;
            $submission->date_received = $review->date_submitted;
            $submission->save(true, ['form_id', 'processed_by_user_id', 'date_received']);
            $review->submission_id = $this->db->lastInsertID;
            $review->save(true, ['submission_id']);
        }
        $this->dropColumn(Review::tableName(), 'date_submitted');
        $this->dropColumn(Review::tableName(), 'author_user_id');
        $this->dropColumn(Review::tableName(), 'author_name');
        $this->dropColumn(Review::tableName(), 'author_phone');
        $this->dropColumn(Review::tableName(), 'rate');
        $this->renameColumn(Review::tableName(), 'text', 'review_text');
        $this->alterColumn(Review::tableName(), 'rating_id', 'CHAR(32)');
        $this->update(
            BackendMenu::tableName(),
            ['route' => 'review/backend-rating/index'],
            ['route' => 'backend/rating/index']
        );
        $this->update(
            BackendMenu::tableName(),
            ['route' => 'review/backend-review/index'],
            ['name' => 'Reviews']
        );
        $this->delete(BackendMenu::tableName(), ['route' => ['review/backend/products', 'review/backend/pages']]);
        $this->alterColumn(RatingValues::tableName(), 'rating_id', 'CHAR(32) NOT NULL');
        $this->alterColumn(RatingValues::tableName(), 'object_id', 'INT UNSIGNED NOT NULL');
        $this->alterColumn(RatingValues::tableName(), 'object_model_id', 'INT UNSIGNED NOT NULL');
        $this->alterColumn(RatingValues::tableName(), 'rating_item_id', 'INT UNSIGNED NOT NULL');
        $this->alterColumn(RatingValues::tableName(), 'user_id', 'INT UNSIGNED NOT NULL');
        $this->createIndex('ix-rating_values-rating_id', RatingValues::tableName(), 'rating_id');
        $this->createIndex(
            'ix-rating_values-object_id-object_model_id',
            RatingValues::tableName(),
            ['object_id', 'object_model_id']
        );
        $this->createIndex('ix-rating_item-rating_group', RatingItem::tableName(), 'rating_group');
    }

    public function down()
    {
        echo "You have no way back.\n";
        return false;
    }
}
