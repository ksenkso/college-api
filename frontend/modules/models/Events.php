<?php

namespace frontend\modules\models;

use Yii;

/**
 * This is the model class for table "events".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $title
 * @property string $description
 * @property integer $timestamp
 * @property integer $type_id
 */
class Events extends \yii\db\ActiveRecord
{


	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'events';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'title', 'timestamp'], 'required'],
            [['user_id', 'timestamp', 'type_id'], 'integer'],
            [['title'], 'string', 'max' => 40],
            [['description'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'title' => 'Title',
            'description' => 'Description',
            'timestamp' => 'Timestamp',
            'type_id' => 'Type ID',
        ];
    }

    public static function findByPeriod($start_date, $end_date, $user_id) {
	    return static::find()
	          ->where(
		          [
			          'and',
			          [
				          'and',
				          ['>=', 'timestamp', $start_date],
				          ['<=', 'timestamp', $end_date]
			          ],
			          ['user_id' => $user_id]
		          ]
	          )
	          ->all();
    }
}
