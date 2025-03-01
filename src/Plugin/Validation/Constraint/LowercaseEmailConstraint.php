<?php

/**
 * Copyright 2024 Google Inc.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * version 2 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301,
 * USA.
 */

namespace Drupal\apigee_edge\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted value is a unique integer.
 *
 * @Constraint(
 *   id = "DeveloperLowercaseEmail",
 *   label = @Translation("Developer Lowercase Email", context = "Validation"),
 *   type = "email"
 * )
 */
class LowercaseEmailConstraint extends Constraint {

  /**
   * The message that will be shown if the value contains any uppercase characters.
   *
   * @var string
   */
  public $notLowercase = 'This email address accepts only lowercase characters.';

}
