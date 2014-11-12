<?php

namespace tests\unit\models;

use app;
use app\tests\fixtures\LinkAnchorsFixture;
use Yii;
use yii\codeception\DbTestCase;

class SortableBehaviorTest extends DbTestCase
{
	use \Codeception\Specify;

	public function fixtures() {
		return [
			'test_link_anchors' => LinkAnchorsFixture::className(),
		];
	}
	public function testReorderAndInitialValues()
	{
		
		$first = new app\models\LinkAnchors([
			'model_name' => 'Page',
			'model_id' => 1,
			'anchor' => 'First anchor',
		]);
		$first->save();

		$second = new app\models\LinkAnchors([
			'model_name' => 'Page',
			'model_id' => 1,
			'anchor' => 'Second anchor',
		]);
		$second->save();

		$unrelated = new app\models\LinkAnchors([
			'model_name' => 'Page',
			'model_id' => 2,
			'anchor' => 'Test-unrelated page anchor',
		]);
		$unrelated->save();

		$this->specify('sort_order by default is equals to autoincremented id value', function () use ($first, $second, $unrelated) {
			expect('First sort_order equals it\'s id', $first->sort_order)->equals( (string) $first->id);
			expect('Second sort_order equals it\'s id', $second->sort_order)->equals( (string) $second->id);
			expect('Unrelated sort_order equals it\'s id', $unrelated->sort_order)->equals( (string) $unrelated->id);
		});

		// sort second previous first
		$second->sortModels([$second->id, $first->id]);

		$first->refresh();
		$second->refresh();
		$unrelated->refresh();
		

		$this->specify('updated sort - second, first, unrelated', function () use ($first, $second, $unrelated) {
			expect('First sort_order equals to id of second', $first->sort_order)->equals( (string) $second->id);
			expect('Second sort_order equals to id of first', $second->sort_order)->equals( (string) $first->id);
			expect('Unrelated sort_order equals it\'s id', $unrelated->sort_order)->equals( (string) $unrelated->id);
		});

		// and back

		$second->sortModels([$first->id, $second->id]);

		$first->refresh();
		$second->refresh();
		$unrelated->refresh();
		

		$this->specify('updated sort - first, second, unrelated', function () use ($first, $second, $unrelated) {
			expect('First sort_order equals to id of first', $first->sort_order)->equals( (string) $first->id);
			expect('Second sort_order equals to id of second', $second->sort_order)->equals( (string) $second->id);
			expect('Unrelated sort_order equals it\'s id', $unrelated->sort_order)->equals( (string) $unrelated->id);
		});

		// move second up
		$second->moveUp();

		$first->refresh();
		$second->refresh();
		$unrelated->refresh();

		$this->specify('move second up', function () use ($first, $second, $unrelated) {
			expect('First sort_order equals to id of second', $first->sort_order)->equals( (string) $second->id);
			expect('Second sort_order equals to id of first', $second->sort_order)->equals( (string) $first->id);
			expect('Unrelated sort_order equals it\'s id', $unrelated->sort_order)->equals( (string) $unrelated->id);
		});

		// move second down
		$second->moveDown();

		$first->refresh();
		$second->refresh();
		$unrelated->refresh();

		$this->specify('move second down - first, second, unrelated', function () use ($first, $second, $unrelated) {
			expect('First sort_order equals to id of first', $first->sort_order)->equals( (string) $first->id);
			expect('Second sort_order equals to id of second', $second->sort_order)->equals( (string) $second->id);
			expect('Unrelated sort_order equals it\'s id', $unrelated->sort_order)->equals( (string) $unrelated->id);
		});
		
		// move unrelated before first
		$unrelated->moveBefore($first);

		$first->refresh();
		$second->refresh();
		$unrelated->refresh();

		$this->specify('move unrelated before first - unrelated, first, second', function () use ($first, $second, $unrelated) {
			expect('First sort_order equals to id of second', $first->sort_order)->equals( (string) $second->id);
			expect('Second sort_order equals to id of unrelated', $second->sort_order)->equals( (string) $unrelated->id);
			expect('Unrelated sort_order equals id of first', $unrelated->sort_order)->equals( (string) $first->id);
		});

		// move unrelated after second
		$unrelated->moveAfter($second);

		$first->refresh();
		$second->refresh();
		$unrelated->refresh();

		$this->specify('move unrelated before first - first, second, unrelated', function () use ($first, $second, $unrelated) {
			expect('First sort_order < second sort_order', (integer) $first->sort_order)->lessThen( (integer) $second->sort_order);
			expect('Second sort_order < unrelated sort_order', (integer) $second->sort_order)->lessThen( (integer) $unrelated->sort_order);
			expect('Unrelated sort_order = second sort_order + 1 ', (integer) $unrelated->sort_order)->equals( intval($second->sort_order) + 1);
		});

		// test sort by default

		$models = app\models\LinkAnchors::find()->sortByDefault()->asArray()->all();
		$this->specify('check sortByDefault', function () use ($models) {
			expect('Total 3 rows in array', count($models))->equals(3);
			expect('First row name anchor is "First anchor"', $models[0]['anchor'])->equals("First anchor");
			expect('Second row name anchor is "Second anchor"', $models[1]['anchor'])->equals("Second anchor");
			expect('Third row name anchor is "Test-unrelated page anchor"', $models[2]['anchor'])->equals("Test-unrelated page anchor");
		});

	}


}
