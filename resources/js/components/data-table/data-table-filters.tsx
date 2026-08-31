import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { DataTableFilterDef } from './types';

/** Radix Select forbids an empty item value, so "All" needs a sentinel. */
const ALL = '__all';

type Props = {
    filters: DataTableFilterDef[];
    /** The currently applied values, keyed by filter `key`. */
    values: Record<string, string>;
    /** Commit the whole draft — fires one request, not one per control. */
    onApply: (next: Record<string, string>) => void;
};

function sameValues(a: Record<string, string>, b: Record<string, string>) {
    const keys = Object.keys(a);

    return (
        keys.length === Object.keys(b).length &&
        keys.every((key) => a[key] === b[key])
    );
}

function hasValues(values: Record<string, string>) {
    return Object.values(values).some((value) => value !== '');
}

/**
 * The collapsible filter card `<DataTable>` shows under its toolbar. Edits stay
 * local until **Apply** — so picking three values still costs one query.
 */
export function DataTableFilters({ filters, values, onApply }: Props) {
    const [draft, setDraft] = useState<Record<string, string>>(values);

    // Re-seed the draft whenever the applied values change from outside (the
    // browser back button, "Clear all") — same render-phase sync the source
    // hooks use for the search box.
    const [appliedKey, setAppliedKey] = useState(() => JSON.stringify(values));
    const currentKey = JSON.stringify(values);

    if (appliedKey !== currentKey) {
        setAppliedKey(currentKey);
        setDraft(values);
    }

    const setDraftValue = (key: string, value: string | null) => {
        setDraft((current) => {
            const next = { ...current };

            if (value === null || value === '') {
                delete next[key];
            } else {
                next[key] = value;
            }

            return next;
        });
    };

    const dirty = !sameValues(draft, values);

    return (
        <div className="space-y-4 rounded-lg border p-4">
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {filters.map((filter) => (
                    <div key={filter.key} className="grid gap-1.5">
                        <Label className="text-xs text-muted-foreground">
                            {filter.label}
                        </Label>

                        {filter.type === 'select' ? (
                            <Select
                                value={draft[filter.key] ?? ALL}
                                onValueChange={(value) =>
                                    setDraftValue(
                                        filter.key,
                                        value === ALL ? null : value,
                                    )
                                }
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={ALL}>All</SelectItem>
                                    {filter.options.map((option) => (
                                        <SelectItem
                                            key={option.value}
                                            value={option.value}
                                        >
                                            {option.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        ) : (
                            <Input
                                value={draft[filter.key] ?? ''}
                                placeholder={filter.placeholder}
                                onChange={(event) =>
                                    setDraftValue(
                                        filter.key,
                                        event.target.value || null,
                                    )
                                }
                                onKeyDown={(event) => {
                                    if (event.key === 'Enter' && dirty) {
                                        onApply(draft);
                                    }
                                }}
                            />
                        )}
                    </div>
                ))}
            </div>

            <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                {(hasValues(draft) || hasValues(values)) && (
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => {
                            setDraft({});
                            onApply({});
                        }}
                    >
                        Clear all
                    </Button>
                )}

                <Button
                    size="sm"
                    onClick={() => onApply(draft)}
                    disabled={!dirty}
                >
                    Apply
                </Button>
            </div>
        </div>
    );
}
