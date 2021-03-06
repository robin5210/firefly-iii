<?php
/**
 * HasAnyCategoryTest.php
 * Copyright (c) 2017 thegrumpydictator@gmail.com
 *
 * This file is part of Firefly III.
 *
 * Firefly III is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Firefly III is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Firefly III.  If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Tests\Unit\TransactionRules\Triggers;

use FireflyIII\Models\TransactionJournal;
use FireflyIII\TransactionRules\Triggers\HasAnyCategory;
use Tests\TestCase;

/**
 * Class HasAnyCategoryTest
 */
class HasAnyCategoryTest extends TestCase
{
    /**
     * @covers \FireflyIII\TransactionRules\Triggers\HasAnyCategory::triggered
     */
    public function testTriggered()
    {
        $journal  = TransactionJournal::find(25);
        $category = $journal->user->categories()->first();
        $journal->categories()->detach();
        $journal->categories()->save($category);

        $this->assertEquals(1, $journal->categories()->count());
        $trigger = HasAnyCategory::makeFromStrings('', false);
        $result  = $trigger->triggered($journal);
        $this->assertTrue($result);
    }

    /**
     * @covers \FireflyIII\TransactionRules\Triggers\HasAnyCategory::triggered
     */
    public function testTriggeredNot()
    {
        $journal = TransactionJournal::find(24);
        $journal->categories()->detach();
        $this->assertEquals(0, $journal->categories()->count());
        $trigger = HasAnyCategory::makeFromStrings('', false);
        $result  = $trigger->triggered($journal);
        $this->assertFalse($result);
    }

    /**
     * @covers \FireflyIII\TransactionRules\Triggers\HasAnyCategory::triggered
     */
    public function testTriggeredTransactions()
    {
        $journal  = TransactionJournal::find(26);
        $category = $journal->user->categories()->first();
        $journal->categories()->detach();
        $this->assertEquals(0, $journal->categories()->count());

        // append to transaction
        foreach ($journal->transactions()->get() as $index => $transaction) {
            $transaction->categories()->detach();
            if (0 === $index) {
                $transaction->categories()->save($category);
            }
        }

        $trigger = HasAnyCategory::makeFromStrings('', false);
        $result  = $trigger->triggered($journal);
        $this->assertTrue($result);
    }

    /**
     * @covers \FireflyIII\TransactionRules\Triggers\HasAnyCategory::willMatchEverything
     */
    public function testWillMatchEverything()
    {
        $value  = '';
        $result = HasAnyCategory::willMatchEverything($value);
        $this->assertFalse($result);
    }
}
