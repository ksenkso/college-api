<?php

namespace frontend\modules\models;

use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Reader\ODText;
use PhpOffice\PhpWord\Reader\Word2007;
use PhpOffice\PhpWord\Style\Paragraph;
use PhpOffice\PhpWord\TemplateProcessor;
use Yii;

/**
 * This is the model class for table "portfolio".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $record_type
 * @property string $datePlace
 * @property string $content
 * @property string $description
 */
class Portfolio extends \yii\db\ActiveRecord
{

	const TYPE_GRADE = 1;
	const TYPE_WORK = 2;
	const TYPE_COURSE = 3;
	const TYPE_ADDITIONAL = 4;
	const TYPE_OLYMPIADS = 5;
	const TYPE_CONF = 6;
	const TYPE_SPORT = 7;
	const TYPE_CREATIVE = 8;
	const TYPE_ATTACHEMENT = 9;

	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'portfolio';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'record_type', 'content'], 'required'],
            [['user_id', 'record_type'], 'integer'],
            [['datePlace', 'content', 'description'], 'string', 'max' => 255],
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
            'record_type' => 'Record Type',
            'datePlace' => 'Date Place',
            'content' => 'Content',
            'description' => 'Description',
        ];
    }

	private static function groupRecords( $records ) {
		$works = [];
		$grades = [];
		$courses = [];
		$creative = [];
		$sports = [];
		$additional = [];
		$olympiads = [];
		$conferences = [];
		$images = [];

		foreach ( $records as $record ) {
			switch ($record->record_type) {
				case Portfolio::TYPE_GRADE: {
					$grades[] = $record;
					break;
				}
				case Portfolio::TYPE_WORK: {
					$works[] = $record;
					break;
				}
				case Portfolio::TYPE_COURSE: {
					$courses[] = $record;
					break;
				}
				case Portfolio::TYPE_ADDITIONAL: {
					$additional[] = $record;
					break;
				}
				case Portfolio::TYPE_OLYMPIADS: {
					$olympiads[] = $record;
					break;
				}
				case Portfolio::TYPE_CONF: {
					$conferences[] = $record;
					break;
				}
				case Portfolio::TYPE_SPORT: {
					$sports[] = $record;
					break;
				}

				case Portfolio::TYPE_CREATIVE: {
					$creative[] = $record;
					break;
				}
				default: {
					$images[] = $record;
				}
			}
		}

		return [$works,
			$grades,
			$courses,
			$creative,
			$sports,
			$additional,
			$olympiads,
			$conferences,
			$images];
    }

	public static function processPortfolio($user_id) {
		$records = static::find()
			->where([
				'user_id' => $user_id
			])
			->all();

		list($works,
			$grades,
			$courses,
			$creative,
			$sports,
			$additional,
			$olympiads,
			$conferences,
			$images) = static::groupRecords($records);

		/**
		 * @var ODText $reader
		 */
		$reader = IOFactory::createReader();
		$pw = $reader->load(\Yii::getAlias('@app') . '/templates/tpl_portfolio_title.docx');
		$photoSection = $pw->addSection();
		$photoSection->addImage(\Yii::getAlias('@app') . '/attachments/test.jpg');

		$photoSection->addPageBreak();


		$tp = new TemplateProcessor(\Yii::getAlias('@app') . '/templates/tst.docx');

		// Grades

		$tp->cloneRow('i', count($grades));

		foreach ( $grades as $i => $grade ) {
			$index = $i+1;

			$tp->setValue("i#$index", $index);
			$tp->setValue("lessonName#$index", $grade->content);
			$tp->setValue("lessonMark#$index", $grade->description);
		}

		// Works

		$tp->cloneRow('wi', count($works));

		foreach ( $works as $i => $work ) {
			$parts = explode('&', $work->description);
			$index = $i+1;

			$tp->setValue("wi#$index", $index);
			$tp->setValue("workName#$index", $work->content);
			$tp->setValue("chef#$index", $parts[0]);
			$tp->setValue("workMark#$index", $parts[1]);
			$tp->setValue("reviewer#$index", $parts[2]);
		}

		// Courses

		$tp->cloneRow('ci', count($courses));

		foreach ( $courses as $i => $course ) {
			$index = $i+1;

			$tp->setValue("ci#$index", $index);
			$tp->setValue("courseName#$index", $course->content);
			$tp->setValue("courseHours#$index", $course->description);
		}

		// Additional programs

		$tp->cloneRow('ai', count($additional));

		foreach ( $additional as $i => $add ) {
			$index = $i+1;
			$parts = explode('&', $add->description);

			$tp->setValue("ai#$index", $index);
			$tp->setValue("programName#$index", $add->content);
			$tp->setValue("programHours#$index", $parts[0]);
			$tp->setValue("programPlace#$index", $parts[1]);
		}

		// Olympiads

		$tp->cloneRow('oi', count($olympiads));

		foreach ( $olympiads as $i => $olympiad ) {
			$index = $i+1;
			$parts = explode('&', $olympiad->description);

			$tp->setValue("oi#$index", $index);
			$tp->setValue("olName#$index", $olympiad->content);
			$tp->setValue("olPlace#$index", $parts[0]);
			$tp->setValue("olResult#$index", $parts[1]);
		}

		// Conferences

		$tp->cloneRow('coni', count($conferences));

		foreach ( $conferences as $i => $conference ) {
			$index = $i+1;
			$parts = explode('&', $conference->description);

			$tp->setValue("coni#$index", $index);
			$tp->setValue("confName#$index", $conference->content);
			$tp->setValue("confDate#$index", $parts[0]);
			$tp->setValue("confResult#$index", $parts[1]);
		}

		// Sports

		$tp->cloneRow('si', count($sports));

		foreach ( $sports as $i => $sport ) {
			$index = $i+1;
			$parts = explode('&', $sport->description);

			$tp->setValue("si#$index", $index);
			$tp->setValue("sportName#$index", $sport->content);
			$tp->setValue("sportDate#$index", $parts[0]);
			$tp->setValue("sportResult#$index", $parts[1]);
		}

		// Creative

		$tp->cloneRow('cri', count($creative));

		foreach ( $creative as $i => $cr ) {
			$index = $i+1;
			$parts = explode('&', $cr->description);

			$tp->setValue("cri#$index", $index);
			$tp->setValue("creativeName#$index", $cr->content);
			$tp->setValue("creativeDate#$index", $parts[0]);
			$tp->setValue("creativeResult#$index", $parts[1]);
		}

		$user = User::findOne($user_id);

		$filename = 'Портфолио_' . $user->last_name . substr($user->first_name, 0, 2) . '.docx';
		$tp->saveAs($filename);


		return $filename;
	}
}
