<?php
use yii\db\Migration;
use app\models\AttractionChannel;
/**
 * Class m190122_083236_fill_attraction_channels
 */
class m190122_083236_fill_attraction_channels extends Migration
{
    protected $channels = [
        'Аптеки',
        'Киевская ассоциация пляжного футбола',
        'TheBODY school',
        'Карта друга',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->clean();

        foreach($this->channels as $channel) {
            $this->insert('attraction_channel', array(
                'name' => $channel,
                'type' => AttractionChannel::TYPE_OFFLINE,
                'is_active' => 1,
                'integration_type' => null,
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->clean();
    }

    protected function clean()
    {
        foreach($this->channels as $channel) {
            $this->delete('attraction_channel', ['name' => $channel, 'type' => AttractionChannel::TYPE_OFFLINE]);
        }
    }
}
