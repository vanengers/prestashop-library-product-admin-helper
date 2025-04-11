<?php

use Symfony\Component\Form\FormBuilderInterface;

class FormBuilderHelper
{
    /**
     * @param FormBuilderInterface $form
     * @param string $name
     * @return bool
     * @author George van Engers <george@dewebsmid.nl>
     * @since 11-04-2025
     */
    public static function removeForm(FormBuilderInterface $form, string $name): bool
    {
        if ($form->has($name)) {
            $form->remove($name);
            return true;
        }
        else {
            foreach($form->all() as $child) {
                if (self::removeForm($child, $name)) {
                    return true;
                }
            }
        }

        return false;
    }
}