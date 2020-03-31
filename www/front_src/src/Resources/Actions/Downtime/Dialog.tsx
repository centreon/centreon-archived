import * as React from 'react';

import MomentUtils from '@date-io/moment';

import { Typography, Checkbox, FormHelperText, Grid } from '@material-ui/core';
import {
  MuiPickersUtilsProvider,
  KeyboardTimePicker,
  KeyboardDatePicker,
  DatePickerProps,
  TimePickerProps,
} from '@material-ui/pickers';

import { Dialog, TextField, SelectField, Loader } from '@centreon/ui';

import {
  labelCancel,
  labelEndDate,
  labelEndTime,
  labelStartDate,
  labelStartTime,
  labelChangeEndDate,
  labelChangeEndTime,
  labelChangeStartDate,
  labelChangeStartTime,
  labelComment,
  labelDowntime,
  labelDuration,
  labelFixed,
  labelFrom,
  labelHours,
  labelMinutes,
  labelSeconds,
  labelSetDowntime,
  labelSetDowntimeOnServices,
  labelTo,
} from '../../translatedLabels';
import { Resource } from '../../models';

interface Props {
  locale: string | null;
  resources: Array<Resource>;
  canConfirm: boolean;
  onCancel;
  onConfirm;
  errors?;
  values;
  handleChange;
  setFieldValue;
  submitting: boolean;
  loading: boolean;
}

const pickerCommonProps = {
  disableToolbar: true,
  variant: 'inline',
  margin: 'none',
  inputVariant: 'filled',
  TextFieldComponent: TextField,
  InputProps: {
    disableUnderline: true,
  },
};

const datePickerProps = {
  ...pickerCommonProps,
  format: 'LL',
} as DatePickerProps;

const timePickerProps = {
  ...pickerCommonProps,
  ampm: false,
  format: 'LT',
} as TimePickerProps;

const DialogDowntime = ({
  locale,
  resources,
  canConfirm,
  onCancel,
  onConfirm,
  errors,
  values,
  submitting,
  handleChange,
  setFieldValue,
  loading,
}: Props): JSX.Element => {
  const open = resources.length > 0;

  const hasHosts = resources.find((resource) => resource.type === 'host');

  const changeDate = (field) => (value): void => {
    setFieldValue(field, value);
  };

  return (
    <Dialog
      labelCancel={labelCancel}
      labelConfirm={labelSetDowntime}
      labelTitle={labelDowntime}
      open={open}
      onClose={onCancel}
      onCancel={onCancel}
      onConfirm={onConfirm}
      confirmDisabled={!canConfirm}
      submitting={submitting}
    >
      {loading && <Loader fullContent />}
      <MuiPickersUtilsProvider utils={MomentUtils} locale={locale}>
        <Grid direction="column" container spacing={1}>
          <Grid item>
            <FormHelperText>{labelFrom}</FormHelperText>
            <Grid direction="row" container spacing={1}>
              <Grid item style={{ width: 240 }}>
                <KeyboardDatePicker
                  aria-label={labelStartDate}
                  value={values.dateStart}
                  onChange={changeDate('dateStart')}
                  KeyboardButtonProps={{
                    'aria-label': labelChangeStartDate,
                  }}
                  error={errors?.dateStart !== undefined}
                  helperText={errors?.dateStart}
                  {...datePickerProps}
                />
              </Grid>
              <Grid item style={{ width: 200 }}>
                <KeyboardTimePicker
                  aria-label={labelStartTime}
                  value={values.timeStart}
                  onChange={changeDate('timeStart')}
                  KeyboardButtonProps={{
                    'aria-label': labelChangeStartTime,
                  }}
                  error={errors?.timeStart !== undefined}
                  helperText={errors?.timeStart}
                  {...timePickerProps}
                />
              </Grid>
            </Grid>
          </Grid>
          <Grid item>
            <FormHelperText>{labelTo}</FormHelperText>
            <Grid direction="row" container spacing={1}>
              <Grid item style={{ width: 240 }}>
                <KeyboardDatePicker
                  aria-label={labelEndDate}
                  value={values.dateEnd}
                  onChange={changeDate('dateEnd')}
                  KeyboardButtonProps={{
                    'aria-label': labelChangeEndDate,
                  }}
                  error={errors?.dateEnd !== undefined}
                  helperText={errors?.dateEnd}
                  {...datePickerProps}
                />
              </Grid>
              <Grid item style={{ width: 200 }}>
                <KeyboardTimePicker
                  aria-label={labelEndTime}
                  value={values.timeEnd}
                  onChange={changeDate('timeEnd')}
                  KeyboardButtonProps={{
                    'aria-label': labelChangeEndTime,
                  }}
                  error={errors?.timeEnd !== undefined}
                  helperText={errors?.timeEnd}
                  {...timePickerProps}
                />
              </Grid>
            </Grid>
          </Grid>
          <Grid container item direction="column">
            <Grid item container xs alignItems="center">
              <Grid item xs={1}>
                <Checkbox
                  checked={values.fixed}
                  inputProps={{ 'aria-label': labelFixed }}
                  color="primary"
                  onChange={handleChange('fixed')}
                />
              </Grid>
              <Grid item xs>
                <Typography>{labelFixed}</Typography>
              </Grid>
            </Grid>
          </Grid>
          <Grid item>
            <FormHelperText>{labelDuration}</FormHelperText>
            <Grid direction="row" container spacing={1}>
              <Grid item style={{ width: 150 }}>
                <TextField
                  disabled={values.fixed}
                  type="number"
                  onChange={handleChange('duration.value')}
                  value={values.duration.value}
                  error={errors?.duration?.value !== undefined}
                  helperText={errors?.duration?.value}
                />
              </Grid>
              <Grid item style={{ width: 150 }}>
                <SelectField
                  disabled={values.fixed}
                  options={[
                    {
                      id: 'seconds',
                      name: labelSeconds,
                    },
                    {
                      id: 'minutes',
                      name: labelMinutes,
                    },
                    {
                      id: 'hours',
                      name: labelHours,
                    },
                  ]}
                  selectedOptionId={values.duration.unit}
                  onChange={handleChange('duration.unit')}
                />
              </Grid>
            </Grid>
          </Grid>
          <Grid item>
            <TextField
              value={values.comment}
              onChange={handleChange('comment')}
              multiline
              label={labelComment}
              fullWidth
              rows={3}
              error={errors?.comment !== undefined}
              helperText={errors?.comment}
            />
          </Grid>
          {hasHosts && (
            <Grid container item direction="column">
              <Grid item container xs alignItems="center">
                <Grid item xs={1}>
                  <Checkbox
                    checked={values.downtimeAttachedResources}
                    inputProps={{ 'aria-label': labelSetDowntimeOnServices }}
                    color="primary"
                    onChange={handleChange('downtimeAttachedResources')}
                  />
                </Grid>
                <Grid item xs>
                  <Typography>{labelSetDowntimeOnServices}</Typography>
                </Grid>
              </Grid>
            </Grid>
          )}
        </Grid>
      </MuiPickersUtilsProvider>
    </Dialog>
  );
};

export default DialogDowntime;
