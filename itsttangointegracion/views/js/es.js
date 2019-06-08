/**
* 2007-2019  PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    itstuff <info@itstuff.com.ar>
*  @copyright 2007-2019  PrestaShop SA
*  @license commercial license contact itstuff for details
*  
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

//! moment.js locale configuration

;(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined'
      && typeof require === 'function' ? factory(require('../moment')) :
  typeof define === 'function' && define.amd ? define(['../moment'], factory) :
  factory(global.moment)
}(this, (function (moment) { 'use strict';


   var monthsShortDot = 'ene._feb._mar._abr._may._jun._jul._ago._sep._oct._nov._dic.'.split('_'),
       monthsShort = 'ene_feb_mar_abr_may_jun_jul_ago_sep_oct_nov_dic'.split('_');

   var monthsParse = [/^ene/i, /^feb/i, /^mar/i, /^abr/i, /^may/i, /^jun/i, /^jul/i, /^ago/i, /^sep/i, /^oct/i, /^nov/i, /^dic/i];
   var monthsRegex = /^(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre|ene\.?|feb\.?|mar\.?|abr\.?|may\.?|jun\.?|jul\.?|ago\.?|sep\.?|oct\.?|nov\.?|dic\.?)/i;

   var es = moment.defineLocale('es', {
       months : 'enero_febrero_marzo_abril_mayo_junio_julio_agosto_septiembre_octubre_noviembre_diciembre'.split('_'),
       monthsShort : function (m, format) {
           if (!m) {
               return monthsShortDot;
           } else if (/-MMM-/.test(format)) {
               return monthsShort[m.month()];
           } else {
               return monthsShortDot[m.month()];
           }
       },
       monthsRegex : monthsRegex,
       monthsShortRegex : monthsRegex,
       monthsStrictRegex : /^(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)/i,
       monthsShortStrictRegex : /^(ene\.?|feb\.?|mar\.?|abr\.?|may\.?|jun\.?|jul\.?|ago\.?|sep\.?|oct\.?|nov\.?|dic\.?)/i,
       monthsParse : monthsParse,
       longMonthsParse : monthsParse,
       shortMonthsParse : monthsParse,
       weekdays : 'domingo_lunes_martes_miércoles_jueves_viernes_sábado'.split('_'),
       weekdaysShort : 'dom._lun._mar._mié._jue._vie._sáb.'.split('_'),
       weekdaysMin : 'do_lu_ma_mi_ju_vi_sá'.split('_'),
       weekdaysParseExact : true,
       longDateFormat : {
           LT : 'H:mm',
           LTS : 'H:mm:ss',
           L : 'DD/MM/YYYY',
           LL : 'D [de] MMMM [de] YYYY',
           LLL : 'D [de] MMMM [de] YYYY H:mm',
           LLLL : 'dddd, D [de] MMMM [de] YYYY H:mm'
       },
       calendar : {
           sameDay : function () {
               return '[hoy a la' + ((this.hours() !== 1) ? 's' : '') + '] LT';
           },
           nextDay : function () {
               return '[mañana a la' + ((this.hours() !== 1) ? 's' : '') + '] LT';
           },
           nextWeek : function () {
               return 'dddd [a la' + ((this.hours() !== 1) ? 's' : '') + '] LT';
           },
           lastDay : function () {
               return '[ayer a la' + ((this.hours() !== 1) ? 's' : '') + '] LT';
           },
           lastWeek : function () {
               return '[el] dddd [pasado a la' + ((this.hours() !== 1) ? 's' : '') + '] LT';
           },
           sameElse : 'L'
       },
       relativeTime : {
           future : 'en %s',
           past : 'hace %s',
           s : 'unos segundos',
           ss : '%d segundos',
           m : 'un minuto',
           mm : '%d minutos',
           h : 'una hora',
           hh : '%d horas',
           d : 'un día',
           dd : '%d días',
           M : 'un mes',
           MM : '%d meses',
           y : 'un año',
           yy : '%d años'
       },
       dayOfMonthOrdinalParse : /\d{1,2}º/,
       ordinal : '%dº',
       week : {
           dow : 1, // Monday is the first day of the week.
           doy : 4  // The week that contains Jan 4th is the first week of the year.
       }
   });

   return es;

})));