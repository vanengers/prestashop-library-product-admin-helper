<?php

namespace Vanengers\PrestaShopLibrary\ProductAdminHelper\Form;

use Symfony\Component\Form\FormBuilderInterface;

class FormBuilderHelper
{
    /**
     * @param FormBuilderInterface $form
     * @param string $name
     * @param string|null $parentName
     * @return bool
     * @author George van Engers <george@dewebsmid.nl>
     * @since 11-04-2025
     */
    public static function removeForm(
        FormBuilderInterface $form,
        string $name
    ): bool
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

    /**
     * @param FormBuilderInterface $form
     * @param string $name
     * @param string|null $parentName
     * @return bool
     * @author George van Engers <george@dewebsmid.nl>
     * @since 11-04-2025
     */
    public static function removeFormWithParent(
        FormBuilderInterface $form,
        string $name,
        ?string $parentName = null
    ): bool
    {
        if ($form->has($parentName)) {
            return self::removeForm($form->get($parentName), $name);
        }
        else {
            foreach($form->all() as $child) {
                if (self::removeFormWithParent($child, $name, $parentName)) {
                    return true;
                }
            }
        }

        return false;
    }
}