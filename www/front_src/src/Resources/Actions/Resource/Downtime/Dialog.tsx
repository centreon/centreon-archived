import * as React from 'react';

import moment from 'moment-timezone/builds/moment-timezone-with-data-10-year-range';
import MomentUtils from '@date-io/moment';
import { useTranslation } from 'react-i18next';

import {
  Checkbox,
  FormControlLabel,
  FormHelperText,
  Grid,
} from '@material-ui/core';
import {
  MuiPickersUtilsProvider,
  KeyboardTimePicker,
  KeyboardDatePicker,
  DatePickerProps,
  TimePickerProps,
} from '@material-ui/pickers';
import { Alert } from '@material-ui/lab';

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
} from '../../../translatedLabels';
import { Resource } from '../../../models';
import useAclQuery from '../aclQuery';

interface Props {
  canConfirm: boolean;
  errors?;
  handleChange;
  loading: boolean;
  locale: string | null;
  onCancel;
  onConfirm;
  resources: Array<Resource>;
  setFieldValue;
  submitting: boolean;
  timezone: string | null;
  values;
}

const pickerCommonProps = {
  InputProps: {
    disableUnderline: true,
  },
  TextFieldComponent: TextField,
  disableToolbar: true,
  inputVariant: 'filled',
  margin: 'none',
  variant: 'inline',
};

const datePickerProps = {
  ...pickerCommonProps,
  format: 'LL',
} as Omit<DatePickerProps, 'onChange' | 'value'>;

const timePickerProps = {
  ...pickerCommonProps,
  ampm: false,
  format: 'LT',
} as Omit<TimePickerProps, 'onChange' | 'value'>;

const DialogDowntime = ({
  locale,
  timezone,
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
  const { t } = useTranslation();
  const { getDowntimeDeniedTypeAlert, canDowntimeServices } = useAclQuery();

  const open = resources.length > 0;

  const hasHosts = resources.find((resource) => resource.type === 'host');

  const changeDate =
    (field) =>
    (value): void => {
      setFieldValue(field, value);
    };

  React.useEffect(() => {
    moment.locale(locale);
  }, [locale]);

  React.useEffect(() => {
    moment.tz.setDefault(timezone);
  }, [timezone]);

  const deniedTypeAlert = getDowntimeDeniedTypeAlert(resources);

  return (
    <Dialog
      confirmDisabled={!canConfirm}
      labelCancel={t(labelCancel)}
      labelConfirm={t(labelSetDowntime)}
      labelTitle={t(labelDowntime)}
      open={open}
      submitting={submitting}
      onCancel={onCancel}
      onClose={onCancel}
      onConfirm={onConfirm}
    >
      {loading && <Loader fullContent />}
      {deniedTypeAlert && <Alert severity="warning">{deniedTypeAlert}</Alert>}
      <MuiPickersUtilsProvider
        libInstance={moment}
        locale={locale}
        utils={MomentUtils}
      >
        <Grid container direction="column" spacing={1}>
          <Grid item>
            <FormHelperText>{t(labelFrom)}</FormHelperText>
            <Grid container direction="row" spacing={1}>
              <Grid item style={{ width: 240 }}>
                <KeyboardDatePicker
                  KeyboardButtonProps={{
                    'aria-label': t(labelChangeStartDate),
                  }}
                  aria-label={t(labelStartDate)}
                  error={errors?.dateStart !== undefined}
                  helperText={errors?.dateStart}
                  value={values.dateStart}
                  onChange={changeDate('dateStart')}
                  {...datePickerProps}
                />
              </Grid>
              <Grid item style={{ width: 200 }}>
                <KeyboardTimePicker
                  KeyboardButtonProps={{
                    'aria-label': t(labelChangeStartTime),
                  }}
                  aria-label={t(labelStartTime)}
                  error={errors?.timeStart !== undefined}
                  helperText={errors?.timeStart}
                  value={values.timeStart}
                  onChange={changeDate('timeStart')}
                  {...timePickerProps}
                />
              </Grid>
            </Grid>
          </Grid>
          <Grid item>
            <FormHelperText>{t(labelTo)}</FormHelperText>
            <Grid container direction="row" spacing={1}>
              <Grid item style={{ width: 240 }}>
                <KeyboardDatePicker
                  KeyboardButtonProps={{
                    'aria-label': t(labelChangeEndDate),
                  }}
                  aria-label={t(labelEndDate)}
                  error={errors?.dateEnd !== undefined}
                  helperText={errors?.dateEnd}
                  value={values.dateEnd}
                  onChange={changeDate('dateEnd')}
                  {...datePickerProps}
                />
              </Grid>
              <Grid item style={{ width: 200 }}>
                <KeyboardTimePicker
                  KeyboardButtonProps={{
                    'aria-label': t(labelChangeEndTime),
                  }}
                  aria-label={t(labelEndTime)}
                  error={errors?.timeEnd !== undefined}
                  helperText={errors?.timeEnd}
                  value={values.timeEnd}
                  onChange={changeDate('timeEnd')}
                  {...timePickerProps}
                />
              </Grid>
            </Grid>
          </Grid>
          <Grid item>
            <FormControlLabel
              control={
                <Checkbox
                  checked={values.fixed}
                  color="primary"
                  inputProps={{ 'aria-label': t(labelFixed) }}
                  size="small"
                  onChange={handleChange('fixed')}
                />
              }
              label={t(labelFixed)}
            />
          </Grid>
          <Grid item>
            <FormHelperText>{t(labelDuration)}</FormHelperText>
            <Grid container direction="row" spacing={1}>
              <Grid item style={{ width: 150 }}>
                <TextField
                  disabled={values.fixed}
                  error={errors?.duration?.value}
                  type="number"
                  value={values.duration.value}
                  onChange={handleChange('duration.value')}
                />
              </Grid>
              <Grid item style={{ width: 150 }}>
                <SelectField
                  disabled={values.fixed}
                  options={[
                    {
                      id: 'seconds',
                      name: t(labelSeconds),
                    },
                    {
                      id: 'minutes',
                      name: t(labelMinutes),
                    },
                    {
                      id: 'hours',
                      name: t(labelHours),
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
              fullWidth
              multiline
              error={errors?.comment}
              label={t(labelComment)}
              rows={3}
              value={values.comment}
              onChange={handleChange('comment')}
            />
          </Grid>
          {hasHosts && (
            <Grid item>
              <FormControlLabel
                control={
                  <Checkbox
                    checked={
                      canDowntimeServices() && values.downtimeAttachedResources
                    }
                    color="primary"
                    disabled={!canDowntimeServices()}
                    inputProps={{ 'aria-label': labelSetDowntimeOnServices }}
                    size="small"
                    onChange={handleChange('downtimeAttachedResources')}
                  />
                }
                label={t(labelSetDowntimeOnServices)}
              />
            </Grid>
          )}
        </Grid>
      </MuiPickersUtilsProvider>
    </Dialog>
  );
};

export default DialogDowntime;
