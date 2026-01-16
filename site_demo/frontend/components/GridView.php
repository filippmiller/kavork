<?php

namespace frontend\components;

use kartik\grid\GridView as kartikGridView;
use Yii;

class GridView extends kartikGridView
{

  public static function widget($config = [])
  {
    foreach ($config['columns'] as &$col) {
      if (!is_array($col)) {
        $col = [
            'attribute' => $col,
        ];
      }

      $base_option = (!empty($col['contentOptions'])) ? $col['contentOptions'] : [];
      $col['contentOptions'] = function ($model, $key, $index, $column) use ($base_option) {
        if (empty($column->attribute)) {
          if (empty($column->label)){
            return $base_option;
          }
          $base_option['data-label'] = Yii::t('app',$column->label);
        }else{
          $base_option['data-label'] = $model->getAttributeLabel($column->attribute);
        }

        return $base_option;
      };
    };
    //ddd($config['columns']);

    $pdfHeader = '';
    $pdfFooter = '';
    $title = strip_tags($config['panel']['heading']);

    $config['filterSelector'] = empty($config['filterSelector']) ? [] : explode(',', $config['filterSelector']);
    $config['filterSelector'][] = 'select[name="per-page"]';
    $config['filterSelector'] = implode(',', $config['filterSelector']);

    //ddd($config);
    $baseConfig = [

        'exportConfig' => [

          //'showConfirmAlert' => false,
            GridView::HTML => [
                'label' => Yii::t('kvgrid', 'HTML'),
                'icon' => 'fa fa-file-code-o',
                'iconOptions' => ['class' => 'text-dark'],
                'showHeader' => true,
                'showPageSummary' => true,
                'showFooter' => true,
                'showCaption' => true,
                'filename' => Yii::t('kvgrid', 'grid-export'),
                'alertMsg' => Yii::t('kvgrid', 'The HTML export file will be generated for download.'),
                'options' => ['title' => Yii::t('kvgrid', 'Hyper Text Markup Language')],
                'mime' => 'text/html',
                'config' => [
                  //'cssFile' => 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css'
                ]
            ],
            GridView::TEXT => [
                'label' => Yii::t('kvgrid', 'Text'),
                'icon' => 'fa fa-file-text-o',
                'iconOptions' => ['class' => 'text-dark'],
                'showHeader' => true,
                'showPageSummary' => true,
                'showFooter' => true,
                'showCaption' => true,
                'filename' => Yii::t('kvgrid', 'grid-export'),
                'alertMsg' => Yii::t('kvgrid', 'The TEXT export file will be generated for download.'),
                'options' => ['title' => Yii::t('kvgrid', 'Tab Delimited Text')],
                'mime' => 'text/plain',
                'config' => [
                    'colDelimiter' => "\t",
                    'rowDelimiter' => "\r\n",
                ]
            ],
            GridView::CSV => [
                'label' => Yii::t('kvgrid', 'CSV'),
                'icon' => 'fa fa-file-excel-o',
                'iconOptions' => ['class' => 'text-success'],
                'showHeader' => true,
                'showPageSummary' => true,
                'showFooter' => true,
                'showCaption' => true,
                'filename' => Yii::t('kvgrid', 'grid-export'),
                'alertMsg' => Yii::t('kvgrid', 'The CSV export file will be generated for download.'),
                'options' => ['title' => Yii::t('kvgrid', 'Comma Separated Values')],
                'mime' => 'application/csv',
                'config' => [
                    'colDelimiter' => ",",
                    'rowDelimiter' => "\r\n",
                ]
            ],
            GridView::EXCEL => [
                'label' => Yii::t('kvgrid', 'Excel'),
                'icon' => 'fa fa-file-excel-o',
                'iconOptions' => ['class' => 'text-success'],
                'showHeader' => true,
                'showPageSummary' => true,
                'showFooter' => true,
                'showCaption' => true,
                'filename' => Yii::t('kvgrid', 'grid-export'),
                'alertMsg' => Yii::t('kvgrid', 'The EXCEL export file will be generated for download.'),
                'options' => ['title' => Yii::t('kvgrid', 'Microsoft Excel 95+')],
                'mime' => 'application/vnd.ms-excel',
                'config' => [
                    'worksheet' => Yii::t('kvgrid', 'ExportWorksheet'),
                    'cssFile' => ''
                ]
            ],
            GridView::PDF => [
                'label' => Yii::t('kvgrid', 'PDF'),
                'icon' => 'fa fa-file-pdf-o',
                'iconOptions' => ['class' => 'text-danger'],
                'showHeader' => true,
                'showPageSummary' => true,
                'showFooter' => true,
                'showCaption' => true,
                'filename' => Yii::t('kvgrid', 'grid-export'),
                'alertMsg' => Yii::t('kvgrid', 'The PDF export file will be generated for download.'),
                'options' => ['title' => Yii::t('kvgrid', 'Portable Document Format')],
                'mime' => 'application/pdf',
                'config' => [
                    'mode' => 'urf-8',
                    'format' => 'A4-L',
                    'destination' => 'D',
                    'marginTop' => 20,
                    'marginBottom' => 20,
                    'cssInline' => '.kv-wrap{padding:20px;}' .
                        '.kv-align-center{text-align:center;}' .
                        '.kv-align-left{text-align:left;}' .
                        '.kv-align-right{text-align:right;}' .
                        '.kv-align-top{vertical-align:top!important;}' .
                        '.kv-align-bottom{vertical-align:bottom!important;}' .
                        '.kv-align-middle{vertical-align:middle!important;}' .
                        '.kv-page-summary{border-top:4px double #ddd;font-weight: bold;}' .
                        '.kv-table-footer{border-top:4px double #ddd;font-weight: bold;}' .
                        '.kv-table-caption{font-size:1.5em;padding:8px;border:1px solid #ddd;border-bottom:none;}',
                    'methods' => [
                        'SetHeader' => [
                            ['odd' => $pdfHeader, 'even' => $pdfHeader]
                        ],
                        'SetFooter' => [
                            ['odd' => $pdfFooter, 'even' => $pdfFooter]
                        ],
                    ],
                    'options' => [
                        'title' => $title,
                        'subject' => Yii::t('kvgrid', 'PDF export generated by kartik-v/yii2-grid extension'),
                        'keywords' => Yii::t('kvgrid', 'krajee, grid, export, yii2-grid, pdf')
                    ],
                    'contentBefore' => '',
                    'contentAfter' => ''
                ]
            ],

        ],

        'export' => [
            'showConfirmAlert' => false,
            'label' => Yii::t('kvgrid', 'Export'),
            'icon' => 'icon-metro-new-tab'
        ],

    ];

    $config = $config + $baseConfig;
    //ddd($config);
    return kartikGridView::widget($config); // TODO: Change the autogenerated stub
  }
}