import * as React from 'react';

import { useTranslation } from 'react-i18next';
import dayjs from 'dayjs';
import { useAtomValue } from 'jotai/utils';
import { FormikErrors, FormikHandlers, FormikValues } from 'formik';

import { LocalizationProvider, DateTimePicker } from '@mui/lab';
import {
  Checkbox,
  FormControlLabel,
  FormHelperText,
  Alert,
  TextFieldProps,
  Stack,
} from '@mui/material';
import { Box } from '@mui/system';

import { Dialog, TextField, SelectField } from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

import {
  labelCancel,
  labelEndTime,
  labelComment,
  labelDowntime,
  labelDuration,
  labelFixed,
  labelHours,
  labelMinutes,
  labelSeconds,
  labelSetDowntime,
  labelSetDowntimeOnServices,
  labelTo,
  labelStartTime,
} from '../../../translatedLabels';
import { Resource } from '../../../models';
import useAclQuery from '../aclQuery';
import useDateTimePickerAdapter from '../../../useDateTimePickerAdapter';

import { DowntimeFormValues } from '.';
import { isNil } from 'ramda';

const maxEndDate = new Date('2100-01-01');

interface Props extends Pick<FormikHandlers, 'handleChange'> {
  canConfirm: boolean;
  errors?: FormikErrors<DowntimeFormValues>;
  handleChange;
  onCancel: () => void;
  onConfirm: () => Promise<unknown>;
  resources: Array<Resource>;
  setFieldValue;
  submitting: boolean;
  values: FormikValues;
}

const renderDateTimePickerEndAdornment = (InputProps) => (): JSX.Element =>
  <div>{InputProps?.endAdornment}</div>;

const renderDateTimePickerTextField =
  (ariaLabel: string) =>
  ({ inputRef, inputProps, InputProps }: TextFieldProps): JSX.Element => {
    return (
      <TextField
        EndAdornment={renderDateTimePickerEndAdornment(InputProps)}
        inputProps={{
          ...inputProps,
          'aria-label': ariaLabel,
          ref: inputRef,
          style: { padding: 8 },
        }}
      />
    );
  };

const DialogDowntime = ({
  resources,
  canConfirm,
  onCancel,
  onConfirm,
  errors,
  values,
  submitting,
  handleChange,
  setFieldValue,
}: Props): JSX.Element => {
  const { t } = useTranslation();

  const { getDowntimeDeniedTypeAlert, canDowntimeServices } = useAclQuery();
  const [isPickerOpened, setIsPickerOpened] = React.useState(false);

  const { locale } = useAtomValue(userAtom);

  const { Adapter, getLocalAndConfiguredTimezoneOffset } =
    useDateTimePickerAdapter();

  const open = resources.length > 0;

  const hasHosts = resources.find((resource) => resource.type === 'host');

  const changeDate =
    (field) =>
    (value): void => {
      setFieldValue(field, value);
    };

  const deniedTypeAlert = getDowntimeDeniedTypeAlert(resources);

  const changeTime =
    (field) =>
    (newValue: dayjs.Dayjs | null, keyBoardValue: string | undefined): void => {
      const value = isPickerOpened
        ? dayjs(newValue).toDate()
        : dayjs(keyBoardValue)
            .add(
              dayjs.duration({ hours: getLocalAndConfiguredTimezoneOffset() }),
            )
            .toDate();

      changeDate(field)(value);
    };

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
      {deniedTypeAlert && <Alert severity="warning">{deniedTypeAlert}</Alert>}
      <LocalizationProvider
        dateAdapter={Adapter}
        locale={locale.substring(0, 2)}
      >
        <Stack spacing={1}>
          <Box
            alignItems="center"
            display="grid"
            gap={1}
            gridTemplateColumns="1fr auto 1fr"
          >
            <DateTimePicker<dayjs.Dayjs>
              maxDate={dayjs(maxEndDate)}
              renderInput={renderDateTimePickerTextField(t(labelStartTime))}
              value={values.startTime}
              onChange={changeTime('startTime')}
              onClose={(): void => setIsPickerOpened(false)}
              onOpen={(): void => setIsPickerOpened(true)}
            />
            <FormHelperText>{t(labelTo)}</FormHelperText>
            <DateTimePicker<dayjs.Dayjs>
              renderInput={renderDateTimePickerTextField(t(labelEndTime))}
              value={values.endTime}
              onChange={changeTime('endTime')}
              onClose={(): void => setIsPickerOpened(false)}
              onOpen={(): void => setIsPickerOpened(true)}
            />
            {isNil(errors?.startTime) ? (
                           <div />
            ) : (
              <FormHelperText error>{errors?.startTime}</FormHelperText>
            )}
            <div />
            {isNil(errors?.endTime) ? (
                            <div />
            ) : (
              <FormHelperText error>{errors?.endTime}</FormHelperText>
            )}
          </Box>

          <Stack>
            <FormHelperText>{t(labelDuration)}</FormHelperText>

            <Stack alignItems="center" direction="row" spacing={1}>
              <TextField
                disabled={values.fixed}
                error={errors?.duration?.value}
                type="number"
                value={values.duration.value}
                onChange={handleChange('duration.value')}
              />
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
              <FormControlLabel
                control={
                  <Checkbox
                    checked={
                      canDowntimeServices() && values.isDowntimeWithServices
                    }
                    color="primary"
                    inputProps={{ 'aria-label': t(labelFixed) }}
                    size="small"
                    onChange={handleChange('isDowntimeWithServices')}
                  />
                }
                label={t(labelFixed) as string}
              />
            </Stack>
          </Stack>
          <TextField
            fullWidth
            multiline
            error={errors?.comment}
            label={t(labelComment)}
            rows={3}
            value={values.comment}
            onChange={handleChange('comment')}
          />
          {hasHosts && (
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
              label={t(labelSetDowntimeOnServices) as string}
            />
          )}
        </Stack>
      </LocalizationProvider>
    </Dialog>
  );
};

export default DialogDowntime;
