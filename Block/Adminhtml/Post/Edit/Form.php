<?php
namespace MageAfi\ProductImport\Block\Adminhtml\Post\Edit;
/**
 * Adminhtml Add New Row Form.
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    protected $_wysiwygConfig;
    protected $_options;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_fieldFactory = $fieldFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $model = $this->_coreRegistry->registry('post_data');
        $form = $this->_formFactory->create(
            ['data' => [
                        'id' => 'edit_form',
                        'enctype' => 'multipart/form-data',
                        'action' => $this->getData('action'),
                        'method' => 'post'
                    ]
                ]
        );

        $form->setHtmlIdPrefix('mageafi_productimport_');
        if ($model->getPostId()) {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Edit Post'), 'class' => 'fieldset-wide']
            );
            $fieldset->addField('post_id', 'hidden', ['name' => 'post_id']);
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Add Post'), 'class' => 'fieldset-wide']
            );
        }
        
        $fieldset->addField(
            'user',
            'text',
            [
            'name' => 'user',
            'label' => __('User'),
            'id' => 'user',
            'title' => __('User'),
            'class' => 'required-entry',
            'required' => true,
                ]
        );

        $fieldset->addField(
            'password',
            'text',
            [
            'name' => 'password',
            'label' => __('Password'),
            'id' => 'password',
            'title' => __('Password'),
                ]
        );
      
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        
        
        $this->setForm($form);

        return parent::_prepareForm();
    }
}

