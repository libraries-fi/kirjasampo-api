<?php namespace Nord\ElasticsearchBundle\Console;

use Illuminate\Console\Command;
use Nord\ElasticsearchBundle\Contracts\ElasticsearchServiceContract;
use Nord\ElasticsearchBundle\Documents\Bulk\BulkAction;
use Nord\ElasticsearchBundle\Documents\Bulk\BulkQuery;

abstract class IndexCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Indexes data to an Elasticsearch index.';

    /**
     * @return array
     */
    abstract public function getData();

    /**
     * @return string
     */
    abstract public function getIndex();

    /**
     * @return string
     */
    abstract public function getType();

    /**
     * @param mixed $item
     *
     * @return array
     */
    abstract public function getItemBody($item);

    /**
     * @param mixed $item
     *
     * @return string
     */
    abstract public function getItemId($item);

    /**
     * @param mixed $item
     *
     * @return string
     */
    abstract public function getItemParent($item);


    /**
     * @inheritdoc
     */
    public function handle()
    {
        $this->info('Indexing data ...');

        $service = $this->getElasticsearchService();

        $data = $this->getData();

        $bar = $this->output->createProgressBar(count($data));

        $bulkQuery = new BulkQuery($this->getBulkSize());

        foreach ($data as $item) {
            $action = new BulkAction();

            $meta = [
                '_index' => $this->getIndex(),
                '_type'  => $this->getType(),
                '_id'    => $this->getItemId($item),
            ];

            if (($parent = $this->getItemParent($item)) !== null) {
                $meta['_parent'] = $parent;
            }

            $action->setAction(BulkAction::ACTION_INDEX, $meta)
                ->setBody($this->getItemBody($item));

            $bulkQuery->addAction($action);

            if ($bulkQuery->isReady()) {
                $service->bulk($bulkQuery->toArray());
                $bulkQuery->reset();
            }

            $bar->advance();
        }

        if ($bulkQuery->hasItems()) {
            $service->bulk($bulkQuery->toArray());
        }

        $bar->finish();

        $this->info("\nDone!");

        return 0;
    }


    /**
     * @return int the bulk size (for bulk indexing)
     */
    protected function getBulkSize()
    {
        return BulkQuery::BULK_SIZE_DEFAULT;
    }


    /**
     * @return ElasticsearchServiceContract
     */
    private function getElasticsearchService()
    {
        return app(ElasticsearchServiceContract::class);
    }
}
