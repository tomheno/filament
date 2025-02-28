<?php

namespace Filament\Resources;

use Filament\Filament;
use Filament\Resources\Tables\Table;
use Filament\Resources\Tables\RecordActions;
use Filament\Tables\HasTable;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Str;
use Livewire\Component;

class RelationManager extends Component
{
    use HasTable;

    public static $attachButtonLabel = 'filament::resources/relation-manager.buttons.attach.label';

    public static $attachModalCancelButtonLabel = 'filament::resources/relation-manager.modals.attach.buttons.cancel.label';

    public static $attachModalAttachAnotherButtonLabel = 'filament::resources/relation-manager.modals.attach.buttons.attachAnother.label';

    public static $attachModalAttachButtonLabel = 'filament::resources/relation-manager.modals.attach.buttons.attach.label';

    public static $attachModalAttachedMessage = 'filament::resources/relation-manager.modals.attach.messages.attached';

    public static $attachModalHeading = 'filament::resources/relation-manager.modals.attach.heading';

    public static $createButtonLabel = 'filament::resources/relation-manager.buttons.create.label';

    public static $createModalCancelButtonLabel = 'filament::resources/relation-manager.modals.create.buttons.cancel.label';

    public static $createModalCreateAnotherButtonLabel = 'filament::resources/relation-manager.modals.create.buttons.createAnother.label';

    public static $createModalCreateButtonLabel = 'filament::resources/relation-manager.modals.create.buttons.create.label';

    public static $createModalCreatedMessage = 'filament::resources/relation-manager.modals.create.messages.created';

    public static $createModalHeading = 'filament::resources/relation-manager.modals.create.heading';

    public static $detachButtonLabel = 'filament::resources/relation-manager.buttons.detach.label';

    public static $detachModalCancelButtonLabel = 'filament::resources/relation-manager.modals.detach.buttons.cancel.label';

    public static $detachModalDescription = 'filament::resources/relation-manager.modals.detach.description';

    public static $detachModalDetachButtonLabel = 'filament::resources/relation-manager.modals.detach.buttons.detach.label';

    public static $detachModalDetachedMessage = 'filament::resources/relation-manager.modals.detach.messages.detached';

    public static $detachModalHeading = 'filament::resources/relation-manager.modals.detach.heading';

    public static $editModalCancelButtonLabel = 'filament::resources/relation-manager.modals.edit.buttons.cancel.label';

    public static $editModalHeading = 'filament::resources/relation-manager.modals.edit.heading';

    public static $editModalSaveButtonLabel = 'filament::resources/relation-manager.modals.edit.buttons.save.label';

    public static $editModalSavedMessage = 'filament::resources/relation-manager.modals.edit.messages.saved';

    public static $editRecordActionLabel = 'filament::resources/pages/list-records.table.recordActions.edit.label';

    public static $relationship;

    public $filterable = true;

    public $owner;

    public $searchable = true;

    public $sortable = true;

    protected $listeners = [
        'refreshRelationManagerList' => 'refreshList',
    ];

    public static function getPrimaryColumn()
    {
        return property_exists(static::class, 'primaryColumn') && static::$primaryColumn !== '' ?
            static::$primaryColumn :
            null;
    }

    public static function getRelationship()
    {
        return static::$relationship;
    }

    public static function getTitle()
    {
        if (property_exists(static::class, 'title')) return static::$title;

        return (string) Str::of(static::$relationship)
            ->kebab()
            ->replace('-', ' ')
            ->title();
    }

    public function canAttach()
    {
        if (
            $this->isType(Relations\HasMany::class) ||
            $this->isType(Relations\MorphMany::class)
        ) return false;

        return true;
    }

    public function canCreate()
    {
        return Filament::can('create', $this->getModel());
    }

    public function canDelete()
    {
        return true;
    }

    public function canDeleteSelected()
    {
        return $this->getModel()::find($this->selected)
            ->contains(function ($record) {
                return Filament::can('delete', $record);
            });
    }

    public function canDetachSelected()
    {
        if (
            $this->isType(Relations\HasMany::class) ||
            $this->isType(Relations\MorphMany::class)
        ) return false;

        return true;
    }

    public function deleteSelected()
    {
        abort_unless($this->canDelete(), 403);

        $this->getModel()::destroy(
            $this->getModel()::find($this->selected)
                ->filter(function ($record) {
                    return Filament::can('delete', $record);
                })
                ->toArray(),
        );

        $this->selected = [];
    }

    public function detachSelected()
    {
        $relationship = $this->owner->{$this->getRelationship()}();

        $relationship->detach($this->selected);

        $this->dispatchBrowserEvent('close', static::class . 'RelationManagerDetachModal');
        $this->dispatchBrowserEvent('notify', __(static::$detachModalDetachedMessage));

        $this->selected = [];
    }

    public function getTable()
    {
        return static::table(Table::make())
            ->filterable($this->filterable)
            ->pagination(false)
            ->primaryRecordAction(function ($record) {
                if (! Filament::can('update', $record)) return;

                return 'openEdit';
            })
            ->recordActions([
                RecordActions\Link::make('edit')
                    ->label(static::$editRecordActionLabel)
                    ->action('openEdit')
                    ->when(fn ($record) => Filament::can('update', $record)),
            ])
            ->searchable($this->searchable)
            ->sortable($this->sortable);
    }

    public function getModel()
    {
        return $this->getQuery()->getModel();
    }

    public function getQuery()
    {
        return $this->owner->{static::$relationship}();
    }

    public function isType($type)
    {
        return $this->owner->{$this->getRelationship()}() instanceof $type;
    }

    public function openAttach()
    {
        $this->dispatchBrowserEvent('open', static::class . 'RelationManagerAttachModal');
    }

    public function openCreate()
    {
        $this->dispatchBrowserEvent('open', static::class . 'RelationManagerCreateModal');
    }

    public function openDetach()
    {
        $this->dispatchBrowserEvent('open', static::class . 'RelationManagerDetachModal');
    }

    public function openEdit($record)
    {
        $this->emit('switchRelationManagerEditRecord', static::class, $record);

        $this->dispatchBrowserEvent('open', static::class . 'RelationManagerEditModal');
    }

    public function refreshList($manager = null)
    {
        if ($manager !== null && $manager !== static::class) return;

        $this->callMethod('$refresh');
    }

    public function render()
    {
        return view('filament::relation-manager', [
            'records' => $this->getRecords(),
        ]);
    }
}
