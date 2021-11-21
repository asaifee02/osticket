<div class="row">
<div class="span8">
<?php
    $categories = Category::objects()
        ->exclude(Q::any(array(
            'ispublic'=>Category::VISIBILITY_PRIVATE,
            'faqs__ispublished'=>FAQ::VISIBILITY_PRIVATE,
        )))
        ->annotate(array('faq_count'=>SqlAggregate::COUNT('faqs')))
        ->filter(array('faq_count__gt'=>0));
    if ($categories->exists(true)) { ?>
        <div><?php echo __('Click on the category to browse FAQs.'); ?></div>
        <ul id="kb">
<?php
        foreach ($categories as $C) { ?>
        <!-- Custom Code to implement "Knowledgebase plugin" -->
        <?php
        $category_id = $C->model->ht['category_id'];
            $query2 = "SELECT `value` FROM `".CONFIG_TABLE."` WHERE `namespace`='kb_config' AND `key`='cat-".$category_id."'";
            $res2 = db_query($query2);
            if($res2){
                if(db_num_rows($res2)>0){
                    $row2 = db_fetch_row($res2);
                    $tenants = $row2[0];
                    $tenants = explode(',', $tenants);
                }else{
                    $tenants=array();
                }
            }
        if(in_array($_SERVER['HTTP_HOST'], $tenants)){ ?>
        <!-- Custom Code to implement "Knowledgebase plugin" -->
            <li><i></i>
            <div style="margin-left:45px">
            <h4><?php echo sprintf('<a href="faq.php?cid=%d">%s (%d)</a>',
                $C->getId(), Format::htmlchars($C->getLocalName()), $C->faq_count); ?></h4>
            <div class="faded" style="margin:10px 0">
                <?php echo Format::safe_html($C->getLocalDescriptionWithImages()); ?>
            </div>
<?php       foreach ($C->faqs
                    ->exclude(array('ispublished'=>FAQ::VISIBILITY_PRIVATE))
                    ->limit(5) as $F) { ?>
                <?php $id = KnowledgeBasePlugin::cleanString($F->getQuestion()); //Custom code to make seo friendly FAQ urls ?>
                <div class="popular-faq"><i class="icon-file-alt"></i>
                <a href="faq/<?php echo $id; ?>">
                <?php echo $F->getLocalQuestion() ?: $F->getQuestion(); ?>
                </a></div>
<?php       } ?>
            </div>
            </li>
        <?php } ?><!-- Custom Code to implement "Knowledgebase plugin" -->
<?php   } ?>
       </ul>
<?php
    } else {
        echo __('NO FAQs found');
    }
?>
</div>
<?php /*Custom code to comment search feature to support mobile responsive design
<div class="span4">
    <div class="sidebar">
    <div class="searchbar">
        <form method="get" action="faq.php">
        <input type="hidden" name="a" value="search"/>
        <select name="topicId"  style="width:100%;max-width:100%"
            onchange="javascript:this.form.submit();">
            <option value="">—<?php echo __("Browse by Topic"); ?>—</option>
<?php
$topics = Topic::objects()
    ->annotate(array('has_faqs'=>SqlAggregate::COUNT('faqs')))
    ->filter(array('has_faqs__gt'=>0));
foreach ($topics as $T) { ?>
        <option value="<?php echo $T->getId(); ?>"><?php echo $T->getFullName();
            ?></option>
<?php } ?>
        </select>
        </form>
    </div>
    <br/>
    <div class="content">
        <section>
            <div class="header"><?php echo __('Other Resources'); ?></div>
        </section>
    </div>
    </div>
</div>
*/ ?>
</div>
