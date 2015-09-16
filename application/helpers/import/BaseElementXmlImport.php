<?php
namespace ls\import;

use Cake\Utility\Hash;

/**
 * Class BaseElementXmlImport
 * Base class for XML files that use elements only. (Current LSS format)
 * @package ls\import
 */
abstract class BaseElementXmlImport extends BaseXmlImport
{

    public $parsedDocument;
    public function setSource($file)
    {
        parent::setSource($file);
        $this->parsedDocument = $this->constructTree($this->recurse($this->document->firstChild));
    }

    /**
     * Constructs a tree from an array extracted from an LSS file.
     * @param $data
     */
    protected function constructTree($data) {
        bP();
        $result = $data['surveys']['rows'][0];
        $result['languagesettings'] = $data['surveys_languagesettings']['rows'];
        $languages = isset($result['additional_languages']) && !empty($result['additional_languages']) ? array_merge([$result['language']], array_filter(explode(' ', $result['additional_languages']))) : [$result['language']];
        // Recursion create cleaner code at the cost of some speed (since the $data array is iterated a lot).
        foreach($data['groups']['rows'] as $group) {
            // Only handle the base language.
            if (!isset($group['language']) || $group['language'] == $result['language']) {
                $result['groups'][] = $this->constructGroup($group, $result['language'], $data);
            }
        }
        eP();
        return $result;
    }

    protected function recurse(\DOMNode $node) {
        if ($node->hasChildNodes()) {
            $result = [];
            if ($node->childNodes->length == 1) {
                return $node->firstChild->data;
            }
            foreach ($node->childNodes as $childNode) {
                if ($childNode instanceof \DOMElement) {
                    $recurse = $this->recurse($childNode);
                    if (array_key_exists($childNode->tagName, ['row' => true, 'fieldname' => true])) {
                        $result[] = $recurse;
                    } elseif (!isset($result[$childNode->tagName])) {
                        $result[$childNode->tagName] = $recurse;
                    } elseif(is_array($result[$childNode->tagName]) && isset($result[$childNode->tagName][0])) {
                        $result[$childNode->tagName][] = $recurse;
                    } else {
                        $result[$childNode->tagName] = [$result[$childNode->tagName], $recurse];
                    }
                }
            }

            return $result;
        }
    }

    protected  function constructAnswer($answer, $language, $data) {
        bP();
        // Add translations.
        foreach ($data['answers']['rows'] as $translatedAnswer) {
            if ($this->getCascade($translatedAnswer, ['qid', 'question_id']) == $this->getCascade($answer, ['question_id', 'qid'])
                && $translatedAnswer['code'] == $answer['code']
                && isset($translatedAnswer['language'])
                && ($translatedAnswer['language']) != $language
            ) {
                $answer['translations'][] = $translatedAnswer;
            }
        }
        eP();
        return $answer;
    }
    protected function constructQuestion($question, $language, $data)
    {
        bP();
        // Add translations.
        $questions = isset($data['subquestions']) ? array_merge($data['subquestions']['rows'], $data['questions']['rows']) : $data['questions']['rows'];
        foreach ($questions as $translatedQuestion) {
            if ($translatedQuestion['qid'] == $question['qid']
                && isset($translatedQuestion['language'])
                && $translatedQuestion['language'] != $language
            ) {
                $question['translations'][] = $translatedQuestion;
            }
        }

        // Add subquestions
        foreach (isset($data['subquestions']) ? $data['subquestions']['rows'] : [] as $subQuestion) {
            if ($subQuestion['parent_qid'] == $question['qid'] && (!isset($subQuestion['language']) || $subQuestion['language'] == $language)) {
                $question['subquestions'][] = $this->constructQuestion($subQuestion, $language, $data);
            }
        }

        // Add answers
        if (isset($data['answers'])) {
            foreach ($data['answers']['rows'] as $answer) {
                if ($this->getCascade($answer, ['qid', 'question_id']) == $question['qid']
                    && (!isset($answer['language']) || $answer['language']) == $language
                ) {
                    $question['answers'][] = $this->constructAnswer($answer, $language, $data);
                }
            }
        }

        // Add conditions
        foreach (isset($data['conditions']) ? $data['conditions']['rows'] : [] as $condition) {
            if ($condition['qid'] == $question['qid']) {
                $question['conditions'][] = $condition;
            }
        }

        // Add attributes
        foreach (isset($data['question_attributes']) ? $data['question_attributes']['rows'] : [] as $attribute) {
            if ($attribute['qid'] == $question['qid']) {
                $question[$attribute['attribute']] = $attribute['value'];
            }
        }
        eP();
        return $question;
    }




    /**
     * Creates a tree structure for a specific group.
     * @param $group
     * @param $survey
     * @param $data
     */
    protected function constructGroup($group, $language, $data) {
        bP();
        // Add translations.
        foreach($data['groups']['rows'] as $translatedGroup) {

            if (isset($translatedGroup['gid'],$group['gid'], $translatedGroup['language'])
                && $translatedGroup['gid'] == $group['gid']
                && $translatedGroup['language'] != $language
            ) {
                $group['translations'][] = $translatedGroup;
            } elseif (
                isset($translatedGroup['id'],$group['id'], $translatedGroup['language'])
                && $translatedGroup['id'] == $group['id']
                && $translatedGroup['language'] != $language
            ) {
                $group['translations'][] = $translatedGroup;
            }

        }


        // Add questions.
        foreach($data['questions']['rows'] as $question) {
            // Only handle the base language.
            if ($question['gid'] == (isset($group['gid']) ? $group['gid'] : $group['id']) && (!isset($question['language']) || $question['language'] == $language)) {

                $group['questions'][] = $this->constructQuestion($question, $language, $data);
            }





        }
        eP();
        return $group;

    }

    // Some helper functions.
    protected function getCascade(array $array, array $keys) {
        foreach($keys as $key) {
            if (isset($array[$key])) {
                return $array[$key];
            }
        }
        throw new \Exception('Key not found');
    }
}