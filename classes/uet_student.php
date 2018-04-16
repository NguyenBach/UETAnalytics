<?php
/**
 * Created by PhpStorm.
 * User: bachnguyen
 * Date: 15/04/2018
 * Time: 22:27
 */



namespace mod_uetanalytics;

class uet_student extends uet_user
{
    private $view;
    private $post;
    private $forumview;
    private $forumpost;
    private $submission;
    private $grade;
    private $predict;
    private $course;

    public function __construct($user,$course)
    {
        parent::__construct($user);
        $this->course = new uet_course($course);

    }

    /**
     * @return mixed
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param mixed $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * @return mixed
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param mixed $post
     */
    public function setPost($post)
    {
        $this->post = $post;
    }

    /**
     * @return mixed
     */
    public function getForumview()
    {
        return $this->forumview;
    }

    /**
     * @param mixed $forumview
     */
    public function setForumview($forumview)
    {
        $this->forumview = $forumview;
    }

    /**
     * @return mixed
     */
    public function getForumpost()
    {
        return $this->forumpost;
    }

    /**
     * @param mixed $forumpost
     */
    public function setForumpost($forumpost)
    {
        $this->forumpost = $forumpost;
    }

    /**
     * @return mixed
     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * @param mixed $successsubmission
     */
    public function setSubmission($successsubmission)
    {
        $this->submission = $successsubmission;
    }

    /**
     * @return mixed
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * @param mixed $grade
     */
    public function setGrade($grade)
    {
        $this->grade = $grade;
    }

    /**
     * @return mixed
     */
    public function getPredict()
    {
        return $this->predict;
    }

    /**
     * @param mixed $predict
     */
    public function setPredict($predict)
    {
        $this->predict = $predict;
    }
    
    public function setupStudent(){
        $uet = new uet_analytics($this->course->getCourseId());
        $stat = $uet->getTotalCurrentViewPost($this->getUserId());
        $forum = $uet->getTotalCurrentForumViewPost($this->getUserId());
        $this->setView($stat['view']);
        $this->setPost($stat['post']);
        $this->setForumpost($forum['post']);
        $this->setForumview($forum['view']);
        $submission = $uet->getAssignmentSubmissionInSection($this->course->getCurrentSection(), $this->getUserId());
        $this->setSubmission($submission);
        $this->setPredict($uet->predict($this->getUserId()));
        $this->setGrade($uet->getGrade($this->getUserId()));
    }

}