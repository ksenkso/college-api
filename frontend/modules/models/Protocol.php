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
 * @property integer $type
 * @property integer $count
 * @property integer $number
 */
class Protocol extends \yii\db\ActiveRecord
{

	const PROTOCOL_OUTCLASS = 1;
	const PROTOCOL_PARENTS = 2;

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
            [['user_id', 'theme', 'purposes', 'date', 'plan', 'conclusions', 'type'], 'required'],
            [['user_id', 'date', 'type', 'count', 'number'], 'integer'],
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

	/**
	 * @param $type
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public static function findByType( $type ) {
		return static::find()->where(['type' => $type]);
    }

	public function getProtocolType() {
		return $this->hasOne(ProtocolType::className(), ['id' => 'type']);
    }
}
