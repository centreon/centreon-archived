/* eslint-disable react/prop-types */
/* eslint-disable react/no-unused-prop-types */
import * as React from 'react';

import parse from 'html-react-parser';
import DOMPurify from 'dompurify';
import { useTranslation } from 'react-i18next';

import { makeStyles } from '@material-ui/core';

import { ColumnType, useLocaleDateTimeFormat } from '@centreon/ui';

import {
  labelAuthor,
  labelFixed,
  labelStartTime,
  labelEndTime,
  labelComment,
} from '../../../../translatedLabels';

import DetailsTable, { DetailsTableProps, getYesNoLabel } from '.';

const useStyles = makeStyles({
  comment: {
    display: 'block',
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
  },
});

interface DowntimeDetails {
  comment: string;
  id: number;
}

type Props = Pick<DetailsTableProps, 'endpoint'>;

const DowntimeDetailsTable = ({ endpoint }: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const { toDateTime } = useLocaleDateTimeFormat();

  const columns = [
    {
      getContent: ({ author_name }): string => author_name,
      id: 'author',
      label: t(labelAuthor),
      type: ColumnType.string,
      width: 100,
    },
    {
      getContent: ({ is_fixed }): string => t(getYesNoLabel(is_fixed)),
      id: 'is_fixed',
      label: t(labelFixed),
      type: ColumnType.string,
      width: 100,
    },
    {
      getContent: ({ start_time }): string => toDateTime(start_time),
      id: 'start_time',
      label: t(labelStartTime),
      type: ColumnType.string,
      width: 150,
    },
    {
      getContent: ({ end_time }): string => toDateTime(end_time),
      id: 'end_time',
      label: t(labelEndTime),
      type: ColumnType.string,
      width: 150,
    },

    {
      getContent: ({ comment }: DowntimeDetails): JSX.Element => {
        return (
          <span className={classes.comment}>
            {parse(DOMPurify.sanitize(comment))}
          </span>
        );
      },
      id: 'comment',
      label: t(labelComment),
      type: ColumnType.string,
      width: 250,
    },
  ];

  return (
    <DetailsTable<DowntimeDetails> columns={columns} endpoint={endpoint} />
  );
};

export default DowntimeDetailsTable;
