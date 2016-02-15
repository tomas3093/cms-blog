<?php


class NoticeManager
{
    //vytvori novy oznam
    public function addNotice($noticeText)
    {
        //aktualny cas
        $date = new DateTime();
        $time = $date->getTimestamp();

        $notice = array(
            'notice' => '<i class="fa fa-info-circle"></i> ' . $noticeText,
            'date' => $time
        );
        //ulozenie oznamu do databazy
        Database::insert('notices', $notice);
    }

    //vrati vsetky aktivne oznamy
    public function returnNotices()
    {
        return Database::querryAll('SELECT notice, type, notice_id, date
                FROM notices
                WHERE active = 1
                ORDER BY notice_id DESC
                ');
    }

    //odstrani aktivny oznam (archivuje)
    public function removeNotice($noticeId)
    {
        Database::update('notices', array('active' => 0), 'WHERE notice_id = ?', array($noticeId));
    }
}