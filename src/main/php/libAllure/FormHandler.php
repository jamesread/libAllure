<?php

/*******************************************************************************

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*******************************************************************************/

namespace libAllure;

class FormHandler
{
    private $form = null;
    private $formName;
    private $tpl;
    private $showSidebar = false;
    public $showFooter = true;

    private $constructorArguments;

    public function __construct($formName, \libAllure\Template $tpl = null)
    {
        $this->formName = $formName;
        $this->tpl = $tpl;

        if (empty($this->tpl)) {
            global $tpl;
            $this->tpl = $tpl;
        }

        $this->constructorArguments = range(0, 5);

        $this->setRedirect(null, null);
    }

    public function showSidebar($showSidebar)
    {
        $this->showSidebar = $showSidebar;
    }

    public function constructForm()
    {
        if ($this->form != null) {
            return;
        }

        $this->form = new $this->formName(
            $this->constructorArguments[0],
            $this->constructorArguments[1],
            $this->constructorArguments[2],
            $this->constructorArguments[3],
            $this->constructorArguments[4]
        );
    }

    public function handle()
    {
        $this->constructForm();

        if ($this->form->validate()) {
            $this->form->process();

            /*
            if (!empty($_SESSION['formRedirectUrl'])) {
                redirect($_SESSION['formRedirectUrl'], $_SESSION['formRedirectReason']);
            }
            */
        } else {
            $this->handleRenderForm($this->form);
        }
    }

    protected function handleRenderForm(\libAllure\Form $form)
    {
        require_once 'includes/widgets/header.php';

        if ($this->showSidebar) {
            require_once 'includes/widgets/sidebar.php';
        }

        $this->tpl->assignForm($form);
        $this->tpl->display('form.tpl');

        if ($this->showFooter) {
            require_once 'includes/widgets/footer.php';
        }
    }

    public function setConstructorArgument($id, $value)
    {
        if ($id > 5) {
            throw new \Exception('Max of 5 arguments supported.');
        }

        $this->constructorArguments[$id] = $value;
    }

    public function setRedirect($redirectUrl, $redirectReason = null)
    {
        $_SESSION['formRedirectUrl'] = $redirectUrl;
        $_SESSION['formRedirectReason'] = $redirectReason;

        if (empty($_SESSION['formRedirectReason'])) {
            $_SESSION['formRedirectReason'] = 'You are being redirected.';
        }
    }

    public function getForm()
    {
        return $this->form;
    }
}
