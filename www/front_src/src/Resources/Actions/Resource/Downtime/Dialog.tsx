import { useState } from 'react';

import { useTranslation } from 'react-i18next';
import dayjs from 'dayjs';
import { useAtomValue } from 'jotai/utils';
import { FormikErrors, FormikHandlers, FormikValues } from 'formik';
import { isNil } from 'ramda';

import { LocalizationProvider, DateTimePicker } from '@mui/x-date-pickers';
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
  const [isPickerOpened, setIsPickerOpened] = useState(false);

  const { locale } = useAtomValue(userAtom);

  const {
    Adapter,
    getDestinationAndConfiguredTimezoneOffset,
    formatKeyboardValue,
  } = useDateTimePickerAdapter();

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
        : dayjs(formatKeyboardValue(keyBoardValue))
            .add(
              dayjs.duration({
                hours: getDestinationAndConfiguredTimezoneOffset(),
              }),
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
      <LocalizationProvider
        dateAdapter={Adapter}
        locale={locale.substring(0, 2)}
      >
        {deniedTypeAlert && <Alert severity="warning">{deniedTypeAlert}</Alert>}
        <Stack spacing={2}>
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
              <FormHelperText error>
                {errors?.startTime as string}
              </FormHelperText>
            )}
            <div />
            {isNil(errors?.endTime) ? (
              <div />
            ) : (
              <FormHelperText error>{errors?.endTime as string}</FormHelperText>
            )}
          </Box>

          <Stack>
            <FormHelperText>{t(labelDuration)}</FormHelperText>

            <Stack alignItems="center" direction="row" spacing={1}>
              <TextField
                ariaLabel={t(labelDuration)}
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
                    checked={values.fixed}
                    color="primary"
                    inputProps={{ 'aria-label': t(labelFixed) }}
                    size="small"
                    onChange={handleChange('fixed')}
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
                    canDowntimeServices() && values.isDowntimeWithServices
                  }
                  color="primary"
                  disabled={!canDowntimeServices()}
                  inputProps={{ 'aria-label': labelSetDowntimeOnServices }}
                  size="small"
                  onChange={handleChange('isDowntimeWithServices')}
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
