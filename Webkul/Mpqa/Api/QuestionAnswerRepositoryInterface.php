<?php
 
namespace Webkul\Mpqa\Api;
 
interface QuestionAnswerRepositoryInterface
{
    /**
     * Delete question by product id.
     */
    public function deleteById($productId);
}