<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\SampleTestType;

class SampleAndTests extends Model
{
        use HasFactory;
        protected $table = 'samples_and_tests';
        protected $guarded = ['id'];


        public function samples()
        {
                return $this->hasone(\App\Product::class, 'id', 'sample_id');
        }


        // public function testmethod()
        // {
        //      return $this->hasone(TestGroup::class, 'id' , 'test_id');
        // }

        public function groups()
        {
                return $this->hasone(CustomFieldGroup::class, 'id', 'group_id');
        }

        public function samplereading()
        {
                return $this->hasone(SampleReading::class, 'test_group_id', 'test_id');
        }

        public function testmethod()
        {
                return $this->hasone(TestGroup::class, 'id', 'test_id');
        }

        public function test()
        {
                return $this->hasone(TestGroup::class, 'id', 'test_id');
        }

        public function genericName()
        {
                return $this->belongsTo(GenericName::class, 'generic_name_id');
        }

        //Relation Sub Test
        public function subTest()
        {
                return $this->hasOne(AssociatedTestSubTest::class, 'id', 'sub_test_id');
        }
}
