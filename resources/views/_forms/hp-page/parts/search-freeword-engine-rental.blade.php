<div class="page-element sortable-item element-table" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>" data-has-element="1">
  @include('_forms.hp-page.parts.partials.header', ['element' => $element])

  <div class="page-element-body sortable-item-container ewrapper">
    <table class="table-search">
      <tr>
        <td >
          <dl>
            <dt><?php echo $element->getElement('heading')->getLabel(); echo $view->toolTip('page_parts_searchfreewordenginerental_heading');?>
            </dt>
            <dd>
            <?php  $element->simpleText('heading')?>
            <span class="input-count"></span>
            <div class="errors"></div>
            </dd>
          </dl>
        </td>
      </tr>
      <tr>
        <td >
          <dl>
            <dt><?php echo $element->getElement('path')->getLabel(); echo $view->toolTip('page_parts_searchfreewordenginerental_html_tag');?>
            </dt>
            <dd>
            <?php  $element->simpleText('path')?>
            <div class="errors"></div>
            </dd>
          </dl>
        </td>
      </tr>
    </table>
  </div>
</div>
<style>
.main-contents .table-search input {
background-color: #fff;
}
.table-search{
width: 100%;
}
.table-search tr td{
width: 100%;
padding: 5px 0px;
}

.table-search dt, .table-search dd{
display: inline-block;
}
.table-search dt{
width: 100px;
vertical-align: top;
margin-top: 6px;
}
.table-search dd{
width: calc(100% - 110px);
}
</style>