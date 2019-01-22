<?php
/*
 * Methods to handle banking register
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Bizuno to newer
 * versions in the future. If you wish to customize Bizuno for your
 * needs please refer to http://www.phreesoft.com for more information.
 *
 * @name       Bizuno ERP
 * @author     Dave Premo, PhreeSoft <support@phreesoft.com>
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2018-08-24
 * @filesource /lib/controller/module/phreebooks/register.php
 */

namespace bizuno;

class phreebooksRegister
{
    /**
     * Structure for main entry point of the bank register
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function manager(&$layout=[])
    {
        if (!$security = validateSecurity('phreebooks', 'register', 1)) { return; }
        $title = lang('phreebooks_register');
        $layout = array_replace_recursive($layout, viewMain(), [
            'title'=> $title,
            'datagrid' => ['manager'=>$this->dgRegister('dgRegister', $security)],
            'divs'     => [
                'submenu' => ['order'=>10,'type'=>'html', 'html'=>viewSubMenu('banking')],
                'heading' => ['order'=>30,'type'=>'html', 'html'=>"<h1>$title</h1>"],
                'register'=> ['order'=>70,'label'=>$title,'type'=>'datagrid','key'=>'manager']]]);
    }

    /**
     * Generates the list of register rows for given period
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function managerRows(&$layout=[])
    {
        // add pagination, if page 1 list 19 plus beginning balance
        // if last page, list rest plus ending balance
        // sort by date, deposits first, then withdrawals (credits vs debits?)
        $period = clean('period', ['format'=>'integer','default'=>getModuleCache('phreebooks', 'fy', 'period')], 'post');
        $glAcct = clean('glAcct', ['format'=>'text',   'default'=>getModuleCache('phreebooks', 'settings', 'customers', 'gl_cash')], 'post');
        $balance= dbGetValue(BIZUNO_DB_PREFIX."journal_history", 'beginning_balance', "gl_account='$glAcct' AND period=$period");
        $entries= [['id'=>'0','post_date'=>'','reference'=>'','description'=>lang('beginning_balance'),'debit'=>'','credit'=>'','balance'=>viewFormat($balance, 'currency')]];
        $sql    = "SELECT i.description, m.id, m.journal_id, m.post_date, m.total_amount, m.invoice_num, m.primary_name_b,
           i.debit_amount, i.credit_amount FROM ".BIZUNO_DB_PREFIX."journal_main"." m INNER JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id = i.ref_id
           WHERE m.period='$period' AND i.gl_account='$glAcct' ORDER BY m.post_date, m.invoice_num";
        $stmt   = dbGetResult($sql);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($result as $row) {
          $balance   = $balance + $row['debit_amount'] - $row['credit_amount'];
          $entries[] = [
              'id'         => $row['id'],
              'post_date'  => viewDate($row['post_date']),
              'reference'  => $row['invoice_num'],
              'description'=> $row['primary_name_b']   ? $row['primary_name_b']: $row['description'],
              'debit'      => $row['debit_amount'] <>0 ? viewFormat($row['debit_amount'], 'currency')  : '',
              'credit'     => $row['credit_amount']<>0 ? viewFormat($row['credit_amount'], 'currency') : '',
              'balance'    => viewFormat($balance, 'currency')];
        }
        $entries[] = ['id'=>'999999999','post_date'=>'','reference'=>'','description'=>lang('ending_balance'),'debit'=>'','credit'=>'','balance'=>viewFormat($balance, 'currency')];
        msgDebug("found ".sizeof($entries)." rows");
        $layout = array_replace_recursive($layout, ['content'=>['total'=>sizeof($entries),'rows'=>$entries]]);
    }

    /**
     * Creates the datagrid for the bank register
     * @param string $name - DOM field name
     * @return array - ready to render
     */
    private function dgRegister($name)
    {
        return ['id' => $name,
            'attr'   => ['toolbar'=>"#{$name}Toolbar",'url'=>BIZUNO_AJAX."&p=phreebooks/register/managerRows"],
            'source' => ['filters'=>[
                'period'=> ['order'=>10,'options'=>['width'=>300],'label'=>lang('period'),'break'=>true,'values'=>dbPeriodDropDown(false),'attr'=>['type'=>'select','value'=>getModuleCache('phreebooks', 'fy', 'period')]],
                'glAcct'=> ['order'=>20,'options'=>['width'=>350],'label'=>lang('gl_account'),'values'=>dbGLDropDown(false, ['0']),'attr'=>['type'=>'select','value'=>getModuleCache('phreebooks', 'settings', 'customers', 'gl_cash')]]]],
            'columns'=> [
                'id'         => ['order'=> 0,'attr'=>['hidden'=>true]],
                'post_date'  => ['order'=>10,'label'=>lang('date'),       'attr'=>['resizable'=>true]],
                'reference'  => ['order'=>20,'label'=>lang('reference'),  'attr'=>['resizable'=>true]],
                'description'=> ['order'=>30,'label'=>lang('description'),'attr'=>['resizable'=>true]],
                'debit'      => ['order'=>40,'label'=>lang('deposit'),    'attr'=>['resizable'=>true,'align'=>'right']],
                'credit'     => ['order'=>50,'label'=>lang('payment'),    'attr'=>['resizable'=>true,'align'=>'right']],
                'balance'    => ['order'=>60,'label'=>lang('balance'),    'attr'=>['resizable'=>true,'align'=>'right']]]];
    }
}
