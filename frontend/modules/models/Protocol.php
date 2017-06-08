<?php

namespace frontend\modules\models;

use Yii;

/**
 * This is the model class for table "protocol".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $theme
 * @property string $purposes
 * @property string $form
 * @property integer $date
 * @property string $plan
 * @property string $organization
 * @property string $analysis
 * @property string $conclusions
 */
class Protocol extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'protocol';
	}

	/**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'theme', 'purposes', 'form', 'date', 'plan', 'organization', 'analysis', 'conclusions'], 'required'],
            [['user_id', 'date'], 'integer'],
            [['plan', 'organization', 'analysis', 'conclusions'], 'string'],
            [['theme', 'purposes', 'form'], 'string', 'max' => 255],
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
            'theme' => 'Theme',
            'purposes' => 'Purposes',
            'form' => 'Form',
            'date' => 'Date',
            'plan' => 'Plan',
            'organization' => 'Organization',
            'analysis' => 'Analysis',
            'conclusions' => 'Conclusions',
        ];
    }
}
