<?php

namespace App\Services\Workflow;

class Graph
{
    private $nodes = [];

    private $edges = [];

    public function addNode(string $nodeId)
    {
        $this->nodes[$nodeId] = true;
    }

    public function addEdge(string $from, string $to)
    {
        if (! isset($this->edges[$from])) {
            $this->edges[$from] = [];
        }
        $this->edges[$from][] = $to;
    }

    public function topologicalSort(): array
    {
        $inDegree = array_fill_keys(array_keys($this->nodes), 0);
        foreach ($this->edges as $from => $tos) {
            foreach ($tos as $to) {
                $inDegree[$to]++;
            }
        }

        $queue = new \SplQueue;
        foreach ($inDegree as $nodeId => $degree) {
            if ($degree === 0) {
                $queue->enqueue($nodeId);
            }
        }

        $sorted = [];
        while (! $queue->isEmpty()) {
            $nodeId = $queue->dequeue();
            $sorted[] = $nodeId;

            if (isset($this->edges[$nodeId])) {
                foreach ($this->edges[$nodeId] as $to) {
                    $inDegree[$to]--;
                    if ($inDegree[$to] === 0) {
                        $queue->enqueue($to);
                    }
                }
            }
        }

        if (count($sorted) !== count($this->nodes)) {
            throw new \Exception('Workflow has a cycle.');
        }

        return $sorted;
    }

    public function getSuccessors(string $nodeId): array
    {
        return $this->edges[$nodeId] ?? [];
    }
}
