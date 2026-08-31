import { Form } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import type { RouteFormDefinition } from '@/wayfinder';

type ButtonVariant = React.ComponentProps<typeof Button>['variant'];

type BaseProps = {
    /** Element that opens the dialog — rendered via `DialogTrigger asChild`. */
    trigger: React.ReactNode;
    title: React.ReactNode;
    description: React.ReactNode;
    /** Confirm button label. Default: "Confirm". */
    confirmLabel?: string;
    /** Cancel button label. Default: "Cancel". */
    cancelLabel?: string;
    /** Confirm button variant. Default: "destructive". */
    confirmVariant?: ButtonVariant;
};

type FormConfirmProps = BaseProps & {
    /** Wayfinder form definition, e.g. `RoleController.destroy.form(id)`. */
    form: RouteFormDefinition<'post'>;
    /**
     * Extra values submitted as hidden inputs. Arrays become repeated `name[]`
     * fields — `{ ids: [1, 2] }` → `ids[]=1&ids[]=2`.
     */
    fields?: Record<string, string | number | Array<string | number>>;
    /** Fired once the server has confirmed the action. */
    onConfirmed?: () => void;
    onConfirm?: never;
};

type CallbackConfirmProps = BaseProps & {
    /**
     * Runs when the user confirms. Return a promise to hold the dialog in its
     * pending state until it settles; it closes once the promise resolves.
     */
    onConfirm: () => void | Promise<unknown>;
    form?: never;
    fields?: never;
    onConfirmed?: never;
};

export type ConfirmDialogProps = FormConfirmProps | CallbackConfirmProps;

/**
 * A confirm-before-acting dialog: trigger, title, description, and a
 * Cancel / destructive-Confirm footer. Supply the action either as a Wayfinder
 * `form` (submitted as an Inertia `<Form>`) or an `onConfirm` callback. The
 * dialog owns its open state and closes itself once the action succeeds.
 */
export function ConfirmDialog(props: ConfirmDialogProps) {
    const {
        trigger,
        title,
        description,
        confirmLabel = 'Confirm',
        cancelLabel = 'Cancel',
        confirmVariant = 'destructive',
    } = props;

    const [open, setOpen] = useState(false);
    const [pending, setPending] = useState(false);

    const footer = (submitting: boolean, onConfirm?: () => void) => (
        <DialogFooter className="gap-2">
            <DialogClose asChild>
                <Button variant="secondary">{cancelLabel}</Button>
            </DialogClose>
            {onConfirm ? (
                <Button
                    variant={confirmVariant}
                    disabled={submitting}
                    onClick={onConfirm}
                >
                    {confirmLabel}
                </Button>
            ) : (
                <Button variant={confirmVariant} disabled={submitting} asChild>
                    <button type="submit">{confirmLabel}</button>
                </Button>
            )}
        </DialogFooter>
    );

    const runCallback = async () => {
        if (props.form) {
            return;
        }

        setPending(true);

        try {
            await props.onConfirm();
            setOpen(false);
        } finally {
            setPending(false);
        }
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent>
                <DialogTitle>{title}</DialogTitle>
                <DialogDescription>{description}</DialogDescription>

                {props.form ? (
                    <Form
                        action={props.form.action}
                        method={props.form.method}
                        options={{ preserveScroll: true }}
                        onSuccess={() => {
                            setOpen(false);
                            props.onConfirmed?.();
                        }}
                    >
                        {({ processing }) => (
                            <>
                                {renderHiddenFields(props.fields)}
                                {footer(processing)}
                            </>
                        )}
                    </Form>
                ) : (
                    footer(pending, runCallback)
                )}
            </DialogContent>
        </Dialog>
    );
}

function renderHiddenFields(
    fields:
        Record<string, string | number | Array<string | number>> | undefined,
) {
    if (!fields) {
        return null;
    }

    return Object.entries(fields).flatMap(([name, value]) =>
        Array.isArray(value)
            ? value.map((entry, index) => (
                  <input
                      key={`${name}-${index}`}
                      type="hidden"
                      name={`${name}[]`}
                      value={entry}
                  />
              ))
            : [<input key={name} type="hidden" name={name} value={value} />],
    );
}
