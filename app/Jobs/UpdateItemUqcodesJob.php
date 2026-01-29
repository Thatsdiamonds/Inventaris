<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateItemUqcodesJob implements ShouldQueue
{
    use Queueable;

    protected $type;
    protected $id;
    protected $newCode;
    protected $taskId;
    protected $excludedItems;

    /**
     * Create a new job instance.
     */
    public function __construct($type, $id, $newCode, $taskId = null, $excludedItems = [])
    {
        $this->type = $type;
        $this->id = $id;
        $this->newCode = $newCode;
        $this->taskId = $taskId;
        $this->excludedItems = $excludedItems;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $task = $this->taskId ? \App\Models\BackgroundTask::find($this->taskId) : null;
        if ($task) {
            $task->update(['status' => 'running']);
        }

        try {
            $query = \App\Models\Item::where($this->type . '_id', $this->id);
            
            if (!empty($this->excludedItems)) {
                $query->whereNotIn('id', $this->excludedItems);
            }

            $total = $query->count();
            
            if ($task) {
                $task->update(['total_items' => $total]);
            }

            $query->chunk(100, function ($items) use ($task) {
                foreach ($items as $item) {
                    $parts = explode('.', $item->uqcode);
                    if (count($parts) === 4) {
                        $oldUqcode = $item->uqcode;
                        
                        if ($this->type === 'category') {
                            $parts[1] = $this->newCode;
                        } else {
                            $parts[0] = $this->newCode;
                        }
                        
                        $newUqcode = implode('.', $parts);
                        $item->update(['uqcode' => $newUqcode]);

                        \App\Models\ItemHistory::create([
                            'item_id' => $item->id,
                            'old_uqcode' => $oldUqcode,
                            'new_uqcode' => $newUqcode,
                            'reason' => ucfirst($this->type) . ' unique_code changed (Job Update)',
                        ]);
                    }

                    if ($task) {
                        $task->increment('processed_items');
                    }
                }
            });

            if ($task) {
                $task->update(['status' => 'completed']);
            }
        } catch (\Exception $e) {
            if ($task) {
                $task->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
            throw $e;
        }
    }
}
