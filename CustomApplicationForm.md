# Columns #

## Field Label/Type ##
These options configure the basic information about the field. You should fill in the label you want assigned to the field, what type of field it is, and whether you want the field to appear in the list of applications.

Checking the _Show this field in the Application List?_ will also allow you to filter your application list using this field.

Note: The label is ignored for the 'Blank Space' field type.
## Categories ##
When applying for a job, the form will check which category/categories the job is in, and only display fields that either:
  * Have no category checked
  * Have at least one category checked, and a category in common with the job being applied for.
## Data ##
The default data to include in the field. There are a few things to note about this field:
  * For the 'Checkboxes' and 'Radio Buttons' field types, this is the list of options to be displayed. Each option should be on a new line.
  * This field is ignored for the 'File Upload', 'Heading' and 'Blank Space' field types.
## Submit Filter/Filter Error Message ##
This allows you to reject/accept applications based on certain criteria, and display a custom error message, depending on which criteria failed.
### Rules ###
Each rule should be on a separate line. These rules apply to all fields except 'File Upload', 'Heading' and 'Blank Space'.
  * >_{value}_ - Check that the input is greater than a given value.
  * <_{value}_ - Check that the input is less than a given value.
  * >=_{value}_ - Check that the input is greater than or equal to a given value.
  * <=_{value}_ - Check that the input is less than or equal to a given value.
  * !_{value}_ - Check that the input is not equal to a given value.
There is a special rule, specifically for the 'Date Selector' field. It is for checking that the date submitted is within a certain period from today's date. For example, if your company is only able to hire people older than 18 years, you might create a field labelled 'Date of Birth', with the following rule:
  * >-P18Y
The rule is defined as follows:
  * A > or < sign, depending on if you want to check that the input is outside or inside the defined period, respectively.
  * A - or + sign, depending on if you want to check if the input is in the past or the future, respectively.
  * A 'P', as the starting character of the period definition, followed by one or more of the following, in any order. If they're not defined, they're assumed to be given a value of 0.
    * _{num}_Y - Define the number of years in the period.
    * _{num}_M - Define the number of months in the period.
    * _{num}_D - Define the number of days in the period.
## Sort Order ##
Click the Up/Down buttons to move the field up or down. This will affect where it appears in the public application form.
## Delete ##
Deletes the field. Note that, in order to preserve data, this will not delete the associated data in existing applications.