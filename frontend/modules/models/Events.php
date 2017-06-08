<?php

namespace frontend\modules\models;

/**
 * This is the model class for table "events".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $title
 * @property string $description
 * @property integer $timestamp
 * @property integer $type_id
 * @property integer $report_type
 * @property string $results
 * @property string $responsible
 */
class Events extends \yii\db\ActiveRecord
{

	const DOCUMENT_ANALYSIS = 1;
	const DOCUMENT_DIARY = 2;
	const DOCUMENT_PLAN = 3;
	const DOCUMENT_PARENTS = 4;

	const EVENT_ACTIVITY = 10;
	const EVENT_VISIT = 11;
	const EVENT_PSYCHOLOGIST = 12;
	const EVENT_PARENT_MEET = 13;


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
            [['user_id', 'title', 'timestamp', 'reported'], 'required'],
            [['user_id', 'timestamp', 'type_id'], 'integer'],
            [['title', 'form'], 'string', 'max' => 40],
            [['description', 'responsible', 'results'], 'string', 'max' => 255],
	        [['reported'], 'boolean'],
	        [['report_type'], 'integer']
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
	        'reported' => 'В отчёте',
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
	          ->with(['eventType'])
	          ->all();
    }

    public function fields() {
	 $fields =  parent::fields();
	 $fields['type'] = function($model) {
	 	return $model->eventType;
	 };

	 return $fields;
    }

	public function getEventType()
	{
		return $this->hasOne(EventTypes::className(), ['id' => 'type_id']);
	}
}
