<?php

class ToolsController extends Controller
{
    public function init()
    {
        if (!PexAdmin_Acl::isAllowed('TOOLS'))
            Oxygen_Utils::redirect(Oxygen_Utils::url('home'));

        $this->view->toolbarCurrent = 'tools';
    }

    public function indexAction()
    {

    }

    public function previewChatAction()
    {
        $this->getView()->disableRender();
        $text = $_POST['text'];

        echo utf8_encode(PexAdmin_Utils::convertMinetextToWeb($text));
    }

    public function generateUuidAction()
    {
        $this->getView()->disableRender();
        $playername = $this->getRequest()->getParam('name');

        echo PexAdmin_Utils::generateOfflineUuid($playername);
    }

    public function getMojangUuidAction()
    {
        $this->getView()->disableRender();
        $playername = $this->getRequest()->getParam('name');

        echo PexAdmin_Utils::getMojangUuid($playername);
    }
}
