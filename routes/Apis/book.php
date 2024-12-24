<?php
//批量获取书本信息
Route::any('20220428001', 'BookController@getBooksInfo');
//获取书本所有章节
Route::any('20220428002', 'BookController@getBookChapterAll');
//获取章节内容
Route::any('20220428003', 'BookController@getBookContent');
//书本加入书架
Route::any('20220428004', 'BookController@addBookToBookcase');
//书本移除书架
Route::any('20220428005', 'BookController@removeBookToBookcase');
//上报打开书架时间
Route::any('20240326001', 'BookController@setUserBookReadTime');
//获取用户书架
Route::any('20220428009', 'BookController@getUserBookCase');
//获取书本详情
Route::any('20220429001', 'BookController@getBookInfo');
//分类获取书本信息
Route::any('20220727001', 'BookController@getBooksInfoByCid');
